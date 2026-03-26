<?php

namespace App\Services\Reports\Exporters;

use App\Services\Reports\ReportMeta;
use App\Services\Reports\ReportResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfExporter implements ReportExporter
{
    public function export(array $rows, ReportMeta $meta): ReportResult
    {
        $dir = self::baseDir($meta->generatedBy);
        $filename = self::filename($meta->type, 'pdf');
        $path = $dir . '/' . $filename;

        Storage::makeDirectory($dir);

        // Render formal HTML
        $html = $this->renderHtml($rows, $meta);

        // If Dompdf is available, render real PDF; otherwise fallback to HTML
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            // Use temporary file to avoid passing binary string to filesystem driver
            $tempPath = tempnam(sys_get_temp_dir(), 'pdf_export_');
            file_put_contents($tempPath, $pdfOutput);

            try {
                // putFileAs handles the upload safely from the file path
                Storage::putFileAs($dir, new \Illuminate\Http\File($tempPath), $filename);
            } finally {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }
        } else {
            // Fallback: save HTML so it can still be viewed
            $filename = self::filename($meta->type, 'html');
            $path = $dir . '/' . $filename;
            Storage::put($path, $html);
        }

        return ReportResult::ready(path: $path, filename: $filename, rowsCount: count($rows), meta: $meta);
    }

    private function renderHtml(array $rows, ReportMeta $meta): string
    {
        $title = e($meta->title);
        $generated = now()->toDateTimeString();
        $headers = !empty($rows) ? array_keys($rows[0]) : [];
        $headerImage = $this->resolveHeaderImageDataUri();
        $logoImage = $this->resolveLogoDataUri();

        $thead = '';
        foreach ($headers as $h) { $thead .= '<th>' . e($h) . '</th>'; }

        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>';
            foreach ($headers as $h) { $tbody .= '<td>' . e((string)($row[$h] ?? '')) . '</td>'; }
            $tbody .= '</tr>';
        }

        $headerHtml = '';
        if ($headerImage !== null) {
            $headerHtml = '<div class="banner"><img src="' . $headerImage . '" alt="Holy Name University Header"></div>';
        } else {
            $logoHtml = $logoImage !== null
                ? '<img class="logo" src="' . $logoImage . '" alt="HNU CREaM Logo">'
                : '<div class="logo-placeholder"></div>';

            $headerHtml = '<div class="institution-header">'
                . $logoHtml
                . '<div class="institution-text">'
                . '<div class="uni">HOLY NAME UNIVERSITY</div>'
                . '<div class="values">INTEGRITY • SOCIAL RESPONSIBILITY • EXCELLENCE • EVANGELIZATION • SERVANT LEADERSHIP</div>'
                . '<div class="center">Center for Religious Education and Mission</div>'
                . '</div>'
                . '</div>';
        }

        return "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>{$title}</title>"
            . "<style>"
            . "body{font-family:Arial,sans-serif;color:#1f2937;margin:24px}"
            . ".banner{margin:0 0 14px 0;padding:0}"
            . ".banner img{width:100%;height:auto;display:block}"
            . ".institution-header{display:flex;align-items:center;gap:14px;border-bottom:3px solid #1e7d3c;padding-bottom:10px;margin-bottom:14px}"
            . ".logo{width:62px;height:62px;object-fit:contain}"
            . ".logo-placeholder{width:62px;height:62px;border-radius:31px;background:#e5e7eb}"
            . ".institution-text{line-height:1.2}"
            . ".uni{font-size:32px;letter-spacing:1px;color:#1e7d3c;font-family:'Times New Roman',serif;font-weight:700}"
            . ".values{margin-top:4px;font-size:10px;color:#1e7d3c;font-weight:700;letter-spacing:.2px}"
            . ".center{margin-top:3px;font-size:20px;color:#1e7d3c;font-family:'Times New Roman',serif;font-style:italic;font-weight:700}"
            . "h1{font-size:20px;margin:0 0 6px}"
            . ".meta{margin:0 0 10px;font-size:12px;color:#4b5563}"
            . "table{border-collapse:collapse;width:100%;margin-top:10px}"
            . "th,td{border:1px solid #d1d5db;padding:8px;font-size:12px;vertical-align:top}"
            . "th{background:#f3f4f6;text-align:left;font-weight:700}"
            . "footer{margin-top:18px;font-size:11px;color:#6b7280;border-top:1px solid #e5e7eb;padding-top:8px}"
            . "</style>"
            . "</head><body>{$headerHtml}<h1>{$title}</h1><p class=\"meta\">Generated: {$generated}</p><table><thead><tr>{$thead}</tr></thead><tbody>{$tbody}</tbody></table><footer>CREaM Reports - eReligiousServices</footer></body></html>";
    }

    private function resolveHeaderImageDataUri(): ?string
    {
        $configured = trim((string) config('reports.pdf_header_image', 'hnu-report-header.png'), '/');

        $paths = [
            public_path($configured),
            public_path('hnu-report-header.png'),
            public_path('images/hnu-report-header.png'),
        ];

        foreach ($paths as $path) {
            $dataUri = $this->pathToDataUri($path);
            if ($dataUri !== null) {
                return $dataUri;
            }
        }

        return null;
    }

    private function resolveLogoDataUri(): ?string
    {
        $paths = [
            public_path('images/ers-logo.png'),
            public_path('images/ers-logo.svg'),
        ];

        foreach ($paths as $path) {
            $dataUri = $this->pathToDataUri($path);
            if ($dataUri !== null) {
                return $dataUri;
            }
        }

        return null;
    }

    private function pathToDataUri(string $path): ?string
    {
        if (! File::exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => null,
        };

        if ($mime === null) {
            return null;
        }

        $content = File::get($path);
        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }

    private static function baseDir(int $userId): string
    {
        $date = now()->format('Y-m-d');
        return "reports/{$userId}/{$date}";
    }

    private static function filename(string $type, string $ext): string
    {
        $ts = now()->format('Ymd_His');
        return Str::slug($type) . "_{$ts}.{$ext}";
    }
}

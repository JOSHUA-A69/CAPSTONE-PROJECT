<?php

namespace App\Services\Reports\Exporters;

use App\Services\Reports\ReportMeta;
use App\Services\Reports\ReportResult;
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
            Storage::put($path, $pdfOutput);
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

        $thead = '';
        foreach ($headers as $h) { $thead .= '<th>' . e($h) . '</th>'; }

        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>';
            foreach ($headers as $h) { $tbody .= '<td>' . e((string)($row[$h] ?? '')) . '</td>'; }
            $tbody .= '</tr>';
        }

        return "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>{$title}</title>"
            . "<style>body{font-family:Arial, sans-serif; color:#222} h1{font-size:20px;margin:0 0 8px} p{margin:0 0 12px} table{border-collapse:collapse;width:100%;margin-top:10px} th,td{border:1px solid #ddd;padding:8px;font-size:12px} th{background:#f3f3f3;text-align:left} footer{margin-top:20px;font-size:11px;color:#666}</style>"
            . "</head><body><h1>{$title}</h1><p>Generated: {$generated}</p><table><thead><tr>{$thead}</tr></thead><tbody>{$tbody}</tbody></table><footer>CREaM Reports — eReligiousServices</footer></body></html>";
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

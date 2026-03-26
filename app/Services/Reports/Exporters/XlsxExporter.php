<?php

namespace App\Services\Reports\Exporters;

use App\Services\Reports\ReportMeta;
use App\Services\Reports\ReportResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExporter implements ReportExporter
{
    public function export(array $rows, ReportMeta $meta): ReportResult
    {
        // Fallback to CSV if PhpSpreadsheet not available
        if (!class_exists(Spreadsheet::class)) {
            $csv = new CsvExporter();
            return $csv->export($rows, $meta);
        }

        $dir = self::baseDir($meta->generatedBy);
        $filename = self::filename($meta->type, 'xlsx');
        $path = $dir . '/' . $filename;

        Storage::makeDirectory($dir);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($meta->title, 0, 31));

        $headers = !empty($rows) ? array_keys($rows[0]) : [];
        // Write headers
        foreach ($headers as $i => $h) {
            $sheet->setCellValueExplicit(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1',
                $h,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
        }
        // Bold headers
        if (!empty($headers)) {
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        }

        // Write rows
        $r = 2;
        foreach ($rows as $row) {
            foreach ($headers as $i => $h) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$col}{$r}", (string)($row[$h] ?? ''));
            }
            $r++;
        }

        // Autosize
        foreach (range(1, max(1, count($headers))) as $colIndex) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Write to storage using a temporary file to avoid binary string issues with some drivers
        $writer = new Xlsx($spreadsheet);
        
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_export_');
        $writer->save($tempPath);

        try {
             Storage::putFileAs($dir, new \Illuminate\Http\File($tempPath), $filename);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return ReportResult::ready(path: $path, filename: $filename, rowsCount: count($rows), meta: $meta);
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

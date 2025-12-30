<?php

namespace App\Services\Reports\Exporters;

use App\Services\Reports\ReportMeta;
use App\Services\Reports\ReportResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CsvExporter implements ReportExporter
{
    public function export(array $rows, ReportMeta $meta): ReportResult
    {
        $dir = self::baseDir($meta->generatedBy);
        $filename = self::filename($meta->type, 'csv');
        $path = $dir . '/' . $filename;

        // Ensure directory exists
        Storage::makeDirectory($dir);

        // Create CSV content
        $stream = fopen('php://temp', 'w+');
        if (!empty($rows)) {
            // Write header from keys of first row
            fputcsv($stream, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($stream, array_values($row));
            }
        }
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        Storage::put($path, $contents);

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

<?php

namespace App\Services\Reports\Exporters;

use InvalidArgumentException;

class ExporterFactory
{
    public static function make(string $format): ReportExporter
    {
        return match (strtolower($format)) {
            'csv' => new CsvExporter(),
            'excel' => new XlsxExporter(),
            'pdf' => new PdfExporter(),
            default => throw new InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }
}

interface ReportExporter
{
    /**
     * @param array $rows array of associative arrays
     */
    public function export(array $rows, \App\Services\Reports\ReportMeta $meta): \App\Services\Reports\ReportResult;
}

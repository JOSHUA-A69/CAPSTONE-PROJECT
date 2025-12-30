<?php

namespace App\Services\Reports;

use App\Services\Reports\Filters\ReportFilter;
use App\Services\Reports\Queries\QueryFactory;
use App\Services\Reports\Exporters\ExporterFactory;
use Illuminate\Support\Str;

class ReportService
{
    public function estimateRowCount(string $type, ReportFilter $filter): int
    {
        // Lightweight estimate for deciding sync vs async. For now, return 0 to prefer sync.
        // Implementers can wire this to fast COUNT(*) queries.
        return 0;
    }

    public function generate(string $type, ReportFilter $filter, string $format, int $userId): ReportResult
    {
        $query = QueryFactory::make($type);
        $rows = $query->run($filter);

        $meta = new ReportMeta(
            title: config('reports.types.' . $type . '.label') ?? Str::title(str_replace('_', ' ', $type)),
            type: $type,
            format: $format,
            generatedBy: $userId,
            filters: $filter->toArray(),
        );

        if (empty($rows)) {
            return ReportResult::empty($meta);
        }

        $exporter = ExporterFactory::make($format);
        return $exporter->export($rows, $meta);
    }
}

class ReportMeta
{
    public function __construct(
        public string $title,
        public string $type,
        public string $format,
        public int $generatedBy,
        public array $filters = [],
    ) {
    }
}

class ReportResult
{
    public function __construct(
        public string $status, // ready|empty|failed
        public ?string $path,
        public ?string $filename,
        public int $rowsCount = 0,
        public ?string $message = null,
        public ?ReportMeta $meta = null,
    ) {
    }

    public static function empty(ReportMeta $meta): self
    {
        return new self(status: 'empty', path: null, filename: null, rowsCount: 0, message: 'No Data Found', meta: $meta);
    }

    public static function ready(string $path, string $filename, int $rowsCount, ReportMeta $meta): self
    {
        return new self(status: 'ready', path: $path, filename: $filename, rowsCount: $rowsCount, meta: $meta);
    }

    public static function failed(string $message, ?ReportMeta $meta = null): self
    {
        return new self(status: 'failed', path: null, filename: null, rowsCount: 0, message: $message, meta: $meta);
    }
}

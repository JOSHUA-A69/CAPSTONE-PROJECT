<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Services/Reports/ReportService.php';
require __DIR__ . '/../app/Services/Reports/Exporters/ExporterFactory.php';

use App\Services\Reports\ReportMeta;
use App\Services\Reports\Exporters\XlsxExporter;

$rows = [
    ['A' => 1, 'B' => 2],
    ['A' => 3, 'B' => 4],
];
$meta = new ReportMeta(
    title: 'Test Excel',
    type: 'test_excel',
    format: 'excel',
    generatedBy: 1,
    filters: [],
);

$exporter = new XlsxExporter();
$result = $exporter->export($rows, $meta);

echo "status={$result->status}\n";
echo "path={$result->path}\n";
echo "filename={$result->filename}\n";

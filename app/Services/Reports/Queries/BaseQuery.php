<?php

namespace App\Services\Reports\Queries;

use App\Services\Reports\Filters\ReportFilter;

abstract class BaseQuery implements ReportQuery
{
    protected function noData(): array
    {
        return [];
    }
}

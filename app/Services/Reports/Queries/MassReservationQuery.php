<?php

namespace App\Services\Reports\Queries;

use App\Services\Reports\Filters\ReportFilter;

class MassReservationQuery extends BaseQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        // Placeholder until domain rules are finalized.
        return $this->noData();
    }
}

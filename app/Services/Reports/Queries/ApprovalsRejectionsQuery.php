<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\Organization;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class ApprovalsRejectionsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.schedule_date, '%Y-%m') as period"),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN reservations.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN reservations.status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("COUNT(*) as total"),
            ])
            ->leftJoin('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->groupBy('period')
            ->orderBy('period');

        // Filters
        if ($filter->date_from) {
            $query->where('reservations.schedule_date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $query->where('reservations.schedule_date', '<=', $filter->date_to);
        }
        if (!empty($filter->organizations)) {
            $query->whereIn('reservations.org_id', $filter->organizations);
        }
        if (!empty($filter->services)) {
            $query->whereIn('reservations.service_id', $filter->services);
        }
        if (!empty($filter->adviser_id)) {
            $query->where('organizations.adviser_id', '=', $filter->adviser_id);
        }

        // Optional status filter narrows the set but still returns columns
        if (!empty($filter->status)) {
            $query->where('reservations.status', $filter->status);
        }

        $rows = $query->get()->map(function ($row) {
            $approved = (int) $row->approved;
            $rejected = (int) $row->rejected;
            $total = max((int) $row->total, 1);
            $rate = round(($approved / $total) * 100, 2);
            return [
                'period' => $row->period,
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => (int) $row->pending,
                'total' => (int) $row->total,
                'approval_rate_pct' => $rate,
            ];
        })->toArray();

        return $rows;
    }
}

<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\Organization;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class PerformanceQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                'organizations.org_id',
                'organizations.org_name',
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN reservations.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN reservations.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(DATEDIFF(reservations.schedule_date, reservations.created_at)) as avg_lead_days'),
            ])
            ->join('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->groupBy('organizations.org_id', 'organizations.org_name')
            ->orderBy('organizations.org_name');

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
        if (!empty($filter->status)) {
            $query->where('reservations.status', $filter->status);
        }

        $rows = $query->get()->map(function ($row) {
            $total = max((int) $row->total, 1);
            return [
                'organization' => (string) $row->org_name,
                'approved' => (int) $row->approved,
                'rejected' => (int) $row->rejected,
                'cancelled' => (int) $row->cancelled,
                'total' => (int) $row->total,
                'approval_rate_pct' => round(((int) $row->approved / $total) * 100, 2),
                'cancellation_rate_pct' => round(((int) $row->cancelled / $total) * 100, 2),
                'avg_lead_days' => round((float) $row->avg_lead_days, 2),
            ];
        })->toArray();

        return $rows;
    }
}

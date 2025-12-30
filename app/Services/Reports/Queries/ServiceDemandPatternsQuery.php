<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class ServiceDemandPatternsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.schedule_date, '%Y-%m') as period"),
                'services.service_name as service',
                DB::raw('COUNT(*) as requests'),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN reservations.status IN ('pending','adviser_approved','admin_approved') THEN 1 ELSE 0 END) as pending"),
                DB::raw('ROUND(AVG(DATEDIFF(reservations.schedule_date, reservations.created_at)), 2) as avg_lead_days'),
            ])
            ->leftJoin('services', 'services.service_id', '=', 'reservations.service_id')
            ->groupBy('period', 'services.service_name')
            ->orderBy('period')
            ->orderBy('services.service_name');

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
        if (!empty($filter->status)) {
            $query->where('reservations.status', $filter->status);
        }

        $rows = $query->get()->map(function ($row) {
            $total = max((int) $row->requests, 1);
            $approved = (int) $row->approved;
            return [
                'period' => (string) $row->period,
                'service' => (string) ($row->service ?? '—'),
                'requests' => (int) $row->requests,
                'approved' => $approved,
                'pending' => (int) $row->pending,
                'approval_rate_pct' => round(($approved / $total) * 100, 2),
                'avg_lead_days' => $row->avg_lead_days !== null ? (float) $row->avg_lead_days : null,
            ];
        })->toArray();

        return $rows;
    }
}

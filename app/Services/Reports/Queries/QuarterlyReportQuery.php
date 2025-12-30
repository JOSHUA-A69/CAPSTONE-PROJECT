<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class QuarterlyReportQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                DB::raw('YEAR(reservations.schedule_date) as year'),
                DB::raw('QUARTER(reservations.schedule_date) as quarter'),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN reservations.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN reservations.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN reservations.status IN ('pending','adviser_approved','admin_approved') THEN 1 ELSE 0 END) as pending"),
                DB::raw('COUNT(*) as total'),
            ])
            ->groupBy('year', 'quarter')
            ->orderBy('year')
            ->orderBy('quarter');

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
            return [
                'period' => sprintf('%d-Q%d', (int) $row->year, (int) $row->quarter),
                'approved' => (int) $row->approved,
                'rejected' => (int) $row->rejected,
                'cancelled' => (int) $row->cancelled,
                'pending' => (int) $row->pending,
                'total' => (int) $row->total,
            ];
        })->toArray();

        return $rows;
    }
}

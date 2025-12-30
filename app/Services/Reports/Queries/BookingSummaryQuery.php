<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class BookingSummaryQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $createdQuery = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.created_at, '%Y-%m') as period"),
                DB::raw('COUNT(*) as created'),
            ])
            ->groupBy('period');

        $approvedQuery = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.schedule_date, '%Y-%m') as period"),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
            ])
            ->groupBy('period');

        $cancelledQuery = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.schedule_date, '%Y-%m') as period"),
                DB::raw("SUM(CASE WHEN reservations.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
            ])
            ->groupBy('period');

        // Apply filters consistently
        foreach ([$createdQuery, $approvedQuery, $cancelledQuery] as $q) {
            if ($filter->date_from) {
                // For created we filter by created_at, others by schedule_date; safe fallback
                $column = $q === $createdQuery ? 'reservations.created_at' : 'reservations.schedule_date';
                $q->where($column, '>=', $filter->date_from);
            }
            if ($filter->date_to) {
                $column = $q === $createdQuery ? 'reservations.created_at' : 'reservations.schedule_date';
                $q->where($column, '<=', $filter->date_to);
            }
            if (!empty($filter->organizations)) {
                $q->whereIn('reservations.org_id', $filter->organizations);
            }
            if (!empty($filter->services)) {
                $q->whereIn('reservations.service_id', $filter->services);
            }
            if (!empty($filter->status)) {
                $q->where('reservations.status', $filter->status);
            }
        }

        // Merge per-period results
        $created = $createdQuery->pluck('created', 'period')->toArray();
        $approved = $approvedQuery->pluck('approved', 'period')->toArray();
        $cancelled = $cancelledQuery->pluck('cancelled', 'period')->toArray();

        $periods = array_values(array_unique(array_merge(array_keys($created), array_keys($approved), array_keys($cancelled))));
        sort($periods);

        $rows = [];
        foreach ($periods as $p) {
            $rows[] = [
                'period' => $p,
                'created' => (int) ($created[$p] ?? 0),
                'approved' => (int) ($approved[$p] ?? 0),
                'cancelled' => (int) ($cancelled[$p] ?? 0),
            ];
        }

        return $rows;
    }
}

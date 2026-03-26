<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\LiturgicalSchedule;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class VenueUtilizationAvailabilityTrendsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        // Events (reservations) per venue
        $resQuery = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.schedule_date, '%Y-%m') as period"),
                'venues.name as venue',
                DB::raw('COUNT(*) as reservation_events'),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved_reservations"),
            ])
            ->leftJoin('venues', 'venues.venue_id', '=', 'reservations.venue_id')
            ->groupBy('period', 'venues.name');

        // Staff-plotted schedules per venue with duration (hours)
        $schedQuery = LiturgicalSchedule::query()
            ->from('liturgical_schedules as ls')
            ->select([
                DB::raw("DATE_FORMAT(ls.schedule_date, '%Y-%m') as period"),
                'venues.name as venue',
                DB::raw('COUNT(*) as schedule_events'),
                DB::raw('ROUND(SUM(TIMESTAMPDIFF(MINUTE, ls.start_time, ls.end_time)) / 60, 2) as schedule_hours'),
            ])
            ->leftJoin('venues', 'venues.venue_id', '=', 'ls.venue_id')
            ->groupBy('period', 'venues.name');

        // Apply filters
        foreach ([$resQuery, $schedQuery] as $q) {
            if ($filter->date_from) {
                $column = $q === $resQuery ? 'reservations.schedule_date' : 'ls.schedule_date';
                $q->where($column, '>=', $filter->date_from);
            }
            if ($filter->date_to) {
                $column = $q === $resQuery ? 'reservations.schedule_date' : 'ls.schedule_date';
                $q->where($column, '<=', $filter->date_to);
            }
        }
        if (!empty($filter->organizations)) {
            $resQuery->whereIn('reservations.org_id', $filter->organizations);
        }
        if (!empty($filter->services)) {
            $resQuery->whereIn('reservations.service_id', $filter->services);
        }
        if (!empty($filter->status)) {
            $resQuery->where('reservations.status', $filter->status);
        }

        $resRows = $resQuery->get();
        $schedRows = $schedQuery->get();

        // Merge per venue + period
        // Key: Period|Venue
        $map = [];

        foreach ($resRows as $row) {
            $k = ($row->period ?? '') . '|' . ((string) ($row->venue ?? 'Unassigned'));
            $map[$k] = [
                'period' => (string) $row->period,
                'venue' => (string) ($row->venue ?? 'Unassigned'),
                'res_count' => (int) $row->reservation_events,
                'sched_count' => 0,
                'sched_hours' => 0.0,
            ];
        }

        foreach ($schedRows as $row) {
            $k = ($row->period ?? '') . '|' . ((string) ($row->venue ?? 'Unassigned'));
            if (!isset($map[$k])) {
                $map[$k] = [
                    'period' => (string) $row->period,
                    'venue' => (string) ($row->venue ?? 'Unassigned'),
                    'res_count' => 0,
                    'sched_count' => (int) $row->schedule_events,
                    'sched_hours' => (float) ($row->schedule_hours ?? 0.0),
                ];
            } else {
                $map[$k]['sched_count'] += (int) $row->schedule_events;
                $map[$k]['sched_hours'] += (float) ($row->schedule_hours ?? 0.0);
            }
        }

        // Post-process grouping by Period
        $grouped = collect($map)->sortByDesc('period')->groupBy('period');
        $finalRows = [];

        foreach ($grouped as $period => $items) {
            if (!$period) continue;
            
            $monthObj = \Carbon\Carbon::createFromFormat('Y-m', $period);
            $monthLabel = $monthObj ? $monthObj->format('F Y') : $period;

            // Stats for Header
            $totalRes = $items->sum('res_count');
            $totalSched = $items->sum('sched_count');
            $totalHours = $items->sum('sched_hours');
            $grandTotal = $totalRes + $totalSched;

            // 1. Header Row
            $finalRows[] = [
                'Month' => $monthLabel,
                'Venue' => 'MONTHLY TOTALS',
                'Reservations' => (string) $totalRes,
                'Liturgical Schedules' => "{$totalSched} (" . round($totalHours, 1) . "h)",
                'Total Events' => (string) $grandTotal,
            ];

            // 2. Details per Venue
            foreach ($items->sortBy('venue') as $item) {
                $vRes = (int) $item['res_count'];
                $vSched = (int) $item['sched_count'];
                $vHours = round((float) $item['sched_hours'], 1);
                $vTotal = $vRes + $vSched;

                $finalRows[] = [
                    'Month' => '',
                    'Venue' => $item['venue'],
                    'Reservations' => $vRes > 0 ? (string) $vRes : '—',
                    'Liturgical Schedules' => $vSched > 0 ? "{$vSched} ({$vHours}h)" : '—',
                    'Total Events' => $vTotal > 0 ? (string) $vTotal : '—',
                ];
            }

            // Spacer
            $finalRows[] = array_fill_keys(['Month', 'Venue', 'Reservations', 'Liturgical Schedules', 'Total Events'], '');
        }

        return $finalRows;
    }
}

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
        $key = fn($row) => ($row->period ?? '') . '|' . ((string) ($row->venue ?? 'Unassigned'));
        $map = [];

        foreach ($resRows as $row) {
            $k = $key($row);
            $map[$k] = [
                'period' => (string) $row->period,
                'venue' => (string) ($row->venue ?? 'Unassigned'),
                'reservation_events' => (int) $row->reservation_events,
                'approved_reservations' => (int) $row->approved_reservations,
                'schedule_events' => 0,
                'schedule_hours' => 0.0,
                'total_events' => 0,
            ];
        }

        foreach ($schedRows as $row) {
            $k = $key($row);
            if (!isset($map[$k])) {
                $map[$k] = [
                    'period' => (string) $row->period,
                    'venue' => (string) ($row->venue ?? 'Unassigned'),
                    'reservation_events' => 0,
                    'approved_reservations' => 0,
                    'schedule_events' => (int) $row->schedule_events,
                    'schedule_hours' => (float) ($row->schedule_hours ?? 0.0),
                    'total_events' => 0,
                ];
            } else {
                $map[$k]['schedule_events'] += (int) $row->schedule_events;
                $map[$k]['schedule_hours'] += (float) ($row->schedule_hours ?? 0.0);
            }
        }

        $rows = array_values(array_map(function ($row) {
            $row['total_events'] = (int) $row['reservation_events'] + (int) $row['schedule_events'];
            return $row;
        }, $map));

        // Sort for readability
        usort($rows, function ($a, $b) {
            return [$a['period'], $a['venue']] <=> [$b['period'], $b['venue']];
        });

        return $rows;
    }
}

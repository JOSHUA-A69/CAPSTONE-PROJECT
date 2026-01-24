<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\LiturgicalSchedule;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class OfficiantPersonnelAssignmentsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        // Reservations presided by officiants
        $resQuery = Reservation::query()
            ->select([
                DB::raw("DATE_FORMAT(reservations.schedule_date, '%Y-%m') as period"),
                DB::raw("COALESCE(users.first_name, '') as first_name"),
                DB::raw("COALESCE(users.last_name, '') as last_name"),
                DB::raw("CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')) as officiant"),
                DB::raw('COUNT(*) as reservations_count'),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved_reservations"),
            ])
            ->leftJoin('users', 'users.id', '=', 'reservations.officiant_id')
            ->whereNotNull('reservations.officiant_id')
            ->groupBy('period', 'users.first_name', 'users.last_name');

        // Staff-plotted schedules presided by priests
        $schedQuery = LiturgicalSchedule::query()
            ->from('liturgical_schedules as ls')
            ->select([
                DB::raw("DATE_FORMAT(ls.schedule_date, '%Y-%m') as period"),
                DB::raw("COALESCE(users.first_name, '') as first_name"),
                DB::raw("COALESCE(users.last_name, '') as last_name"),
                DB::raw("CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')) as officiant"),
                DB::raw('COUNT(*) as schedules_count'),
            ])
            ->leftJoin('users', 'users.id', '=', 'ls.priest_id')
            ->whereNotNull('ls.priest_id')
            ->groupBy('period', 'users.first_name', 'users.last_name');

        // Apply shared filters
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

        // Merge workloads by officiant and period
        $key = function ($row) {
            return ($row->period ?? '') . '|' . (trim(($row->officiant ?? '')) ?: 'Unassigned');
        };

        $map = [];
        foreach ($resRows as $row) {
            $k = $key($row);
            $map[$k] = [
                'period' => (string) $row->period,
                'officiant' => trim((string) ($row->officiant ?? '')) ?: 'Unassigned',
                'reservations' => (int) $row->reservations_count,
                'approved_reservations' => (int) $row->approved_reservations,
                'schedules' => 0,
                'total_assignments' => 0,
            ];
        }
        foreach ($schedRows as $row) {
            $k = $key($row);
            if (!isset($map[$k])) {
                $map[$k] = [
                    'period' => (string) $row->period,
                    'officiant' => trim((string) ($row->officiant ?? '')) ?: 'Unassigned',
                    'reservations' => 0,
                    'approved_reservations' => 0,
                    'schedules' => (int) $row->schedules_count,
                    'total_assignments' => 0,
                ];
            } else {
                $map[$k]['schedules'] += (int) $row->schedules_count;
            }
        }

        // Finalize totals
        $rows = array_values(array_map(function ($row) {
            $row['total_assignments'] = (int) $row['reservations'] + (int) $row['schedules'];
            $row['assignment_summary'] = $row['officiant'] . " had " . $row['total_assignments'] . " assignments (" . $row['reservations'] . " reservations, " . $row['schedules'] . " liturgical schedules).";
            return $row;
        }, $map));

        // Sort by period then officiant name
        usort($rows, function ($a, $b) {
            return [$a['period'], $a['officiant']] <=> [$b['period'], $b['officiant']];
        });

        return $rows;
    }
}

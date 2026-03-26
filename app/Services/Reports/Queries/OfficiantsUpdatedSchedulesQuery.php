<?php

namespace App\Services\Reports\Queries;

use App\Models\LiturgicalSchedule;
use App\Models\Reservation;
use App\Models\ReservationHistory;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class OfficiantsUpdatedSchedulesQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        // Recently updated staff-plotted schedules (priests on schedules)
        $sched = LiturgicalSchedule::query()
            ->from('liturgical_schedules as ls')
            ->select([
                DB::raw("DATE(ls.updated_at) as updated_date"),
                DB::raw("TIME(ls.updated_at) as updated_time"),
                DB::raw("CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) as officiant"),
                'ls.title',
                'ls.event_type',
                'ls.schedule_date',
                'ls.start_time',
                'ls.end_time',
                DB::raw("COALESCE(v.name, ls.location) as venue"),
            ])
            ->leftJoin('users as u', 'u.id', '=', 'ls.priest_id')
            ->leftJoin('venues as v', 'v.venue_id', '=', 'ls.venue_id')
            ->whereNotNull('ls.priest_id')
            ->orderBy('ls.updated_at', 'desc');

        // Recently updated reservations with officiant assigned or changed
        $res = Reservation::query()
            ->select([
                DB::raw("DATE(reservations.updated_at) as updated_date"),
                DB::raw("TIME(reservations.updated_at) as updated_time"),
                DB::raw("CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) as officiant"),
                'reservations.activity_name as title',
                DB::raw("'reservation' as event_type"),
                'reservations.schedule_date',
                DB::raw('TIME(reservations.schedule_date) as start_time'),
                DB::raw('NULL as end_time'),
                DB::raw("COALESCE(v.name, reservations.custom_venue_name) as venue"),
            ])
            ->leftJoin('users as u', 'u.id', '=', 'reservations.officiant_id')
            ->leftJoin('venues as v', 'v.venue_id', '=', 'reservations.venue_id')
            ->whereNotNull('reservations.officiant_id')
            ->orderBy('reservations.updated_at', 'desc');

        // Apply date filters
        if ($filter->date_from) {
            $sched->where('ls.updated_at', '>=', $filter->date_from);
            $res->where('reservations.updated_at', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $sched->where('ls.updated_at', '<=', $filter->date_to);
            $res->where('reservations.updated_at', '<=', $filter->date_to);
        }

        // Union two sources
        $rows = $sched->limit(2500)->get()->concat($res->limit(2500)->get())
            ->sortByDesc(function ($row) {
                return $row->updated_date . ' ' . $row->updated_time;
            })
            ->map(function ($row) {
                $typeRaw = (string) ($row->event_type ?? '—');
                $typeLabel = ucwords(str_replace('_', ' ', $typeRaw));

                return [
                    'Updated Date' => (string) $row->updated_date,
                    'Updated Time' => (string) ($row->updated_time ?? ''),
                    'Officiant' => trim((string) ($row->officiant ?? '')) ?: '—',
                    'Title' => (string) ($row->title ?? '—'),
                    'Type' => $typeLabel,
                    'Schedule Date' => (string) ($row->schedule_date ?? ''),
                    'Start Time' => (string) ($row->start_time ?? ''),
                    'End Time' => (string) ($row->end_time ?? ''),
                    'Venue' => (string) ($row->venue ?? '—'),
                ];
            })
            ->values()
            ->toArray();

        return $rows;
    }
}

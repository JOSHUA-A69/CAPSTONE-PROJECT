<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class PendingOrCancelledRequestsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                'reservations.reservation_id',
                DB::raw("DATE(reservations.schedule_date) as date"),
                DB::raw("TIME(reservations.schedule_date) as time"),
                'reservations.status',
                'services.service_name as service',
                'venues.name as venue',
                DB::raw("CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')) as requester"),
                'organizations.org_name as organization',
                'reservations.activity_name',
                'reservations.purpose',
            ])
            ->leftJoin('services', 'services.service_id', '=', 'reservations.service_id')
            ->leftJoin('venues', 'venues.venue_id', '=', 'reservations.venue_id')
            ->leftJoin('users', 'users.id', '=', 'reservations.user_id')
            ->leftJoin('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->where(function ($q) {
                $q->whereIn('reservations.status', ['pending', 'adviser_approved', 'admin_approved', 'cancelled']);
            })
            ->orderBy('reservations.schedule_date');

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

        $rows = $query->get()->map(function ($row) {
            return [
                'date' => (string) $row->date,
                'time' => (string) ($row->time ?? ''),
                'status' => (string) $row->status,
                'service' => (string) ($row->service ?? '—'),
                'venue' => (string) ($row->venue ?? '—'),
                'organization' => (string) ($row->organization ?? '—'),
                'requester' => trim((string) ($row->requester ?? '')) ?: '—',
                'activity' => (string) ($row->activity_name ?? '—'),
                'purpose' => (string) ($row->purpose ?? '—'),
            ];
        })->toArray();

        return $rows;
    }
}

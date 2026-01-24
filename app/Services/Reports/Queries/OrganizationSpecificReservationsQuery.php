<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class OrganizationSpecificReservationsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                DB::raw("DATE(reservations.schedule_date) as date"),
                DB::raw("TIME(reservations.schedule_date) as time"),
                'reservations.status',
                'organizations.org_name as organization',
                'services.service_name as service',
                DB::raw("COALESCE(venues.name, reservations.custom_venue_name) as venue"),
                DB::raw("CONCAT(COALESCE(requester.first_name,''),' ',COALESCE(requester.last_name,'')) as requester"),
                DB::raw("CONCAT(COALESCE(officiant.first_name,''),' ',COALESCE(officiant.last_name,'')) as officiant"),
                'reservations.activity_name as activity',
                'reservations.purpose',
            ])
            ->leftJoin('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->leftJoin('services', 'services.service_id', '=', 'reservations.service_id')
            ->leftJoin('venues', 'venues.venue_id', '=', 'reservations.venue_id')
            ->leftJoin('users as requester', 'requester.id', '=', 'reservations.user_id')
            ->leftJoin('users as officiant', 'officiant.id', '=', 'reservations.officiant_id')
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
        if (!empty($filter->status)) {
            $query->where('reservations.status', $filter->status);
        }
        if (!empty($filter->adviser_id)) {
            $query->where('organizations.adviser_id', '=', $filter->adviser_id);
        }

        $rows = $query->limit(1000)->get()->map(function ($row) {
            $dateFull = \Carbon\Carbon::parse($row->date . ' ' . $row->time);
            $dateStr = $dateFull->format('M d, Y h:i A');

            $statusRaw = (string) ($row->status ?? '');
            $status = $statusRaw !== '' ? ucwords(str_replace('_', ' ', $statusRaw)) : '—';
            
            $org = (string) ($row->organization ?? '—');
            $serviceName = $row->service ?? 'Event';
            $activity = (string) ($row->activity ?? $serviceName);
            
            $venue = (string) ($row->venue ?? '—');
            $requester = trim((string) ($row->requester ?? '')) ?: 'Unknown User';
            $officiant = trim((string) ($row->officiant ?? '')) ?: '—';
            $purpose = (string) ($row->purpose ?? '');

            // Consolidate minor details
            $detailsList = [];
            if ($venue !== '—') $detailsList[] = "Venue: {$venue}";
            if ($requester !== 'Unknown User') $detailsList[] = "By: {$requester}";
            if ($officiant !== '—') $detailsList[] = "Officiant: {$officiant}";
            if ($purpose) $detailsList[] = "Purpose: {$purpose}";

            $detailsStr = implode(' | ', $detailsList);

            return [
                'Date' => $dateStr,
                'Organization' => $org,
                'Activity' => $activity,
                'Status' => $status,
                'Details' => $detailsStr,
            ];
        })->toArray();

        return $rows;
    }
}

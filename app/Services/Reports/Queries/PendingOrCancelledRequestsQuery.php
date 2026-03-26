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

        $rows = $query->limit(1000)->get()->map(function ($row) {
            $dateFull = \Carbon\Carbon::parse($row->date . ' ' . $row->time);
            $dateStr = $dateFull->format('M d, Y h:i A');
            
            $statusRaw = $row->status;
            // Clean up status labels
            $statusLabel = match($statusRaw) {
                'adviser_approved' => 'Adviser Approved (Pending Admin)',
                'admin_approved' => 'Approved', // Should normally not be here if filtering pending/cancelled, but logic allows it
                default => ucwords($statusRaw),
            };

            $venue = (string) ($row->venue ?? '—');
            $requester = trim((string) ($row->requester ?? '')) ?: 'Unknown User';
            $serviceName = $row->service ?? 'Event';
            $activity = (string) ($row->activity_name ?? $serviceName);
            $purpose = (string) ($row->purpose ?? '');
            
            // Build detailed description string
            $detailsParts = [];
            if ($requester !== 'Unknown User') {
                $detailsParts[] = "By: {$requester}";
            }
            if ($venue !== '—') {
                $detailsParts[] = "Venue: {$venue}";
            }
            if ($purpose) {
                $detailsParts[] = "Purpose: {$purpose}";
            }
            
            $details = implode(' | ', $detailsParts);

            return [
                'Date' => $dateStr,
                'Organization' => (string) ($row->organization ?? '—'),
                'Activity' => $activity,
                'Status' => $statusLabel,
                'Details' => $details,
            ];
        })->toArray();

        return $rows;
    }
}

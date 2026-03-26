<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\ReservationHistory;
use App\Services\Reports\Filters\ReportFilter;

class OrganizationActivitiesQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = ReservationHistory::query()
            ->select([
                'reservation_history.reservation_id',
                'reservation_history.action',
                'reservation_history.created_at',
                'reservation_history.remarks',
                'organizations.org_name as organization',
                'services.service_name as service',
                'reservations.activity_name',
            ])
            ->join('reservations', 'reservations.reservation_id', '=', 'reservation_history.reservation_id')
            ->leftJoin('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->leftJoin('services', 'services.service_id', '=', 'reservations.service_id')
            ->whereNull('reservation_history.archived_at')
            ->orderBy('reservation_history.created_at', 'desc');

        if ($filter->date_from) {
            $query->where('reservation_history.created_at', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $query->where('reservation_history.created_at', '<=', $filter->date_to);
        }
        if (!empty($filter->organizations)) {
            $query->whereIn('reservations.org_id', $filter->organizations);
        }
        if (!empty($filter->services)) {
            $query->whereIn('reservations.service_id', $filter->services);
        }
        if (!empty($filter->adviser_id)) {
            $query->join('organizations as org_auth', 'org_auth.org_id', '=', 'reservations.org_id')
                  ->where('org_auth.adviser_id', '=', $filter->adviser_id);
        }

        // Limit to 1000 to prevent PDF memory exhaustion
        $rows = $query->limit(1000)->get()->map(function ($row) {
            $dateStr = optional($row->created_at)->format('Y-m-d H:i');
            $orgName = $row->organization ?? '—';
            $activityName = $row->activity_name ?? $row->service ?? 'Event';
            
            // Format Action: "admin_approved" -> "Admin Approved"
            $actionLabel = ucwords(str_replace('_', ' ', $row->action));

            // Formal Description
            $remarks = $row->remarks ?? '';
            // Remove technical prefixes if present (e.g., "Ref: ")
            if (str_starts_with($remarks, 'Ref: ')) {
                $remarks = substr($remarks, 5);
            }
            
            // Ensure strictly formal tone
            $description = $remarks ?: "The reservation state was updated to {$actionLabel}.";

            return [
                'Date' => $dateStr,
                'Organization' => $orgName,
                'Activity' => $activityName,
                'Action' => $actionLabel,
                'Description' => $description,
            ];
        })->toArray();

        return $rows;
    }
}

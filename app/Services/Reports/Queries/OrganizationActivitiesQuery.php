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
            // Filter by adviser via organizations table
            $query->join('organizations', 'organizations.org_id', '=', 'reservations.org_id')
                  ->where('organizations.adviser_id', '=', $filter->adviser_id);
        }

        $rows = $query->limit(5000)->get()->map(function ($row) {
            return [
                'date' => optional($row->created_at)->toDateTimeString(),
                'reservation' => (string) ($row->activity_name ?? ('#' . (int) $row->reservation_id)),
                'action' => (string) $row->action,
                'organization' => (string) ($row->organization ?? '—'),
                'service' => (string) ($row->service ?? '—'),
            ];
        })->toArray();

        return $rows;
    }
}

<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class OfficiantAllocationsByGroupQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                'organizations.org_name as organization',
                DB::raw("CONCAT(COALESCE(users.first_name,''),' ',COALESCE(users.last_name,'')) as officiant"),
                DB::raw('COUNT(*) as reservations'),
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved_reservations"),
            ])
            ->join('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->leftJoin('users', 'users.id', '=', 'reservations.officiant_id')
            ->whereNotNull('reservations.officiant_id')
            ->groupBy('organizations.org_name', 'users.first_name', 'users.last_name')
            ->orderBy('organizations.org_name')
            ->orderBy('users.last_name');

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

        $rows = $query->get()->map(function ($row) {
            $total = max((int) $row->reservations, 1);
            $approved = (int) $row->approved_reservations;
            return [
                'organization' => (string) ($row->organization ?? '—'),
                'officiant' => trim((string) ($row->officiant ?? '')) ?: '—',
                'reservations' => (int) $row->reservations,
                'approved_reservations' => $approved,
                'approval_rate_pct' => round(($approved / $total) * 100, 2),
            ];
        })->toArray();

        return $rows;
    }
}

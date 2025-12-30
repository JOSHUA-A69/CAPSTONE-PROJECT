<?php

namespace App\Services\Reports\Queries;

use App\Models\OrganizationBookingRequest as OBR;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class OrganizationBookingRequestsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $q = OBR::query()
            ->select([
                DB::raw("DATE_FORMAT(COALESCE(submitted_at, requested_date), '%Y-%m') as period"),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw('COUNT(*) as total'),
                // Average response time (hours) for those with adviser_responded_at
                DB::raw('ROUND(AVG(CASE WHEN adviser_responded_at IS NOT NULL AND submitted_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, submitted_at, adviser_responded_at) END), 2) as avg_response_hours'),
            ])
            ->leftJoin('organizations', 'organizations.org_id', '=', 'organization_booking_requests.organization_id')
            ->groupBy('period')
            ->orderBy('period');

        // Filters
        if ($filter->date_from) {
            $q->where(DB::raw('COALESCE(submitted_at, requested_date)'), '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $q->where(DB::raw('COALESCE(submitted_at, requested_date)'), '<=', $filter->date_to);
        }
        if (!empty($filter->organizations)) {
            $q->whereIn('organization_booking_requests.organization_id', $filter->organizations);
        }
        // services filter does not apply (no service_id column); ignore gracefully
        if (!empty($filter->status)) {
            $q->where('organization_booking_requests.status', $filter->status);
        }
        if (!empty($filter->adviser_id)) {
            $q->where('organizations.adviser_id', '=', $filter->adviser_id);
        }

        $rows = $q->get()->map(function ($row) {
            $total = max((int) $row->total, 1);
            $approved = (int) $row->approved;
            return [
                'period' => (string) $row->period,
                'approved' => $approved,
                'rejected' => (int) $row->rejected,
                'pending' => (int) $row->pending,
                'total' => (int) $row->total,
                'approval_rate_pct' => round(($approved / $total) * 100, 2),
                'avg_response_hours' => $row->avg_response_hours !== null ? (float) $row->avg_response_hours : null,
            ];
        })->toArray();

        return $rows;
    }
}

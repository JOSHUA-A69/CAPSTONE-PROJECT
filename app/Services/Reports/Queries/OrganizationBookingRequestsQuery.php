<?php

namespace App\Services\Reports\Queries;

use App\Models\OrganizationBookingRequest as OBR;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class OrganizationBookingRequestsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = OBR::query()
            ->select([
                'organization_booking_requests.*',
                'organizations.org_name',
                DB::raw("CONCAT(COALESCE(req.first_name,''), ' ', COALESCE(req.last_name,'')) as requestor_name"),
            ])
            ->join('organizations', 'organizations.org_id', '=', 'organization_booking_requests.organization_id')
            ->leftJoin('users as req', 'req.id', '=', 'organization_booking_requests.requestor_id')
            ->orderBy('organization_booking_requests.submitted_at', 'desc');

        // Filters
        if ($filter->date_from) {
            $query->where(DB::raw('COALESCE(submitted_at, requested_date)'), '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $query->where(DB::raw('COALESCE(submitted_at, requested_date)'), '<=', $filter->date_to);
        }
        if (!empty($filter->organizations)) {
            $query->whereIn('organization_booking_requests.organization_id', $filter->organizations);
        }
        if (!empty($filter->status)) {
            $query->where('organization_booking_requests.status', $filter->status);
        }
        if (!empty($filter->adviser_id)) {
            $query->where('organizations.adviser_id', '=', $filter->adviser_id);
        }

        $rows = $query->limit(1000)->get()->map(function ($row) {
            $dateStr = $row->requested_date ? \Carbon\Carbon::parse($row->requested_date)->format('Y-m-d') : '—';
            $statusLabel = ucwords($row->status);
            $orgName = $row->org_name ?? '—';
            $activity = $row->activity_name ?: 'Event';
            
            // Build Description / Details
            $descriptionParts = [];
            if ($row->requestor_name) {
                $descriptionParts[] = "Requested by: {$row->requestor_name}";
            }
            if ($row->requested_venue) {
                $descriptionParts[] = "Venue: {$row->requested_venue}";
            }
            // Removed response time as it is a metric, not a descriptive detail
            if ($row->rejection_reason) {
                $descriptionParts[] = "Reason: {$row->rejection_reason}";
            }
            
            $details = implode(' | ', $descriptionParts);

            return [
                'Date' => $dateStr,
                'Organization' => $orgName,
                'Activity' => $activity,
                'Status' => $statusLabel,
                'Details' => $details,
            ];
        })->toArray();

        return $rows;
    }
}

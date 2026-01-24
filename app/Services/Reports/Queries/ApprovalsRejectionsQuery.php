<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\Organization;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class ApprovalsRejectionsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                'reservations.schedule_date',
                'reservations.status',
                'reservations.activity_name',
                'services.service_name',
                'organizations.org_name',
                DB::raw("CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')) as requester_name"),
            ])
            ->leftJoin('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->leftJoin('users', 'users.id', '=', 'reservations.user_id')
            ->leftJoin('services', 'services.service_id', '=', 'reservations.service_id')
            ->whereIn('reservations.status', ['approved', 'rejected']) // Focus on approvals/rejections
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
        if (!empty($filter->adviser_id)) {
            $query->where('organizations.adviser_id', '=', $filter->adviser_id);
        }

        $allReservations = $query->get();

        // Group by Month
        $grouped = $allReservations->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->schedule_date)->format('Y-m');
        });

        $finalRows = [];

        foreach ($grouped as $monthKey => $items) {
            $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y');

            $total = $items->count();
            $approved = $items->where('status', 'approved')->count();
            $rejected = $items->where('status', 'rejected')->count();
            
            $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

            // 1. Summary Row
            $finalRows[] = [
                'Period' => $monthLabel,
                'Type' => 'PERIOD SUMMARY',
                'Details / Reason' => "Total Decisions: {$total} | Approved: {$approved} | Rejected: {$rejected} | Approval Rate: {$approvalRate}%",
                'Date' => '',
                'Organization' => '',
                'Status' => '',
            ];

            // 2. Detail Rows
            foreach ($items as $item) {
                $statusLabel = ucwords($item->status);
                $activity = $item->activity_name ?: $item->service_name ?: 'Event';
                $date = \Carbon\Carbon::parse($item->schedule_date)->format('M d, Y');
                
                // For rejections, we might add a generic reason or formatted text if we had the history column here,
                // but for now keeping it simple as per schema.
                
                $finalRows[] = [
                    'Period' => $monthLabel,
                    'Type' => 'Reservation Decision',
                    'Details / Reason' => $activity,
                    'Date' => $date,
                    'Organization' => $item->org_name ?? '—',
                    'Status' => $statusLabel,
                ];
            }

            // Spacer
            $finalRows[] = [
                'Period' => '', 'Type' => '', 'Details / Reason' => '', 'Date' => '', 'Organization' => '', 'Status' => ''
            ];
        }

        return $finalRows;
    }
}

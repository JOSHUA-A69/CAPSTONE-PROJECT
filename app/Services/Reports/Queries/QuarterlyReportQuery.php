<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class QuarterlyReportQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                'reservations.schedule_date',
                'reservations.status',
                'reservations.activity_name',
                'reservations.purpose',
                'services.service_name',
                'organizations.org_name',
                DB::raw("CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')) as requester_name"),
            ])
            ->leftJoin('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->leftJoin('users', 'users.id', '=', 'reservations.user_id')
            ->leftJoin('services', 'services.service_id', '=', 'reservations.service_id')
            ->orderBy('reservations.schedule_date');

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

        $allReservations = $query->get();

        // Group by Year-Quarter
        $grouped = $allReservations->groupBy(function ($item) {
            $date = \Carbon\Carbon::parse($item->schedule_date);
            return $date->year . '-' . $date->quarter;
        });

        $finalRows = [];

        foreach ($grouped as $key => $items) {
            list($year, $quarter) = explode('-', $key);
            $periodLabel = "Q{$quarter} {$year}";

            // Calculate stats for this quarter
            $total = $items->count();
            $approved = $items->where('status', 'approved')->count();
            $rejected = $items->where('status', 'rejected')->count();
            $cancelled = $items->where('status', 'cancelled')->count();
            $pending = $items->whereIn('status', ['pending', 'adviser_approved', 'admin_approved'])->count();

            // 1. Add Summary Row for the Quarter
            $finalRows[] = [
                'Period' => $periodLabel,
                'Type' => 'PERIOD SUMMARY',
                'Activity / Details' => "Total: {$total} | Approved: {$approved} | Rejected: {$rejected} | Cancelled: {$cancelled} | Pending: {$pending}",
                'Date' => '',
                'Organization' => '',
                'Status' => '',
            ];

            // 2. Add Detail Rows
            foreach ($items as $item) {
                $statusLabel = ucwords(str_replace('_', ' ', $item->status));
                $activity = $item->activity_name ?: $item->service_name ?: 'Event';
                $date = \Carbon\Carbon::parse($item->schedule_date)->format('M d, Y');
                
                $finalRows[] = [
                    'Period' => $periodLabel,
                    'Type' => 'Reservation',
                    'Activity / Details' => $activity,
                    'Date' => $date,
                    'Organization' => $item->org_name ?? '—',
                    'Status' => $statusLabel,
                ];
            }
            
            // Add a spacer row (empty) for readability between quarters in Excel/CSV
            $finalRows[] = [
                'Period' => '', 'Type' => '', 'Activity / Details' => '', 'Date' => '', 'Organization' =>'', 'Status' => ''
            ];
        }

        return $finalRows;
    }
}

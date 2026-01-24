<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class ReservationStatisticsQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $base = Reservation::query();

        // Filters
        if ($filter->date_from) {
            $base->where('reservations.schedule_date', '>=', $filter->date_from);
        }
        if ($filter->date_to) {
            $base->where('reservations.schedule_date', '<=', $filter->date_to);
        }
        if (!empty($filter->organizations)) {
            $base->whereIn('reservations.org_id', $filter->organizations);
        }
        if (!empty($filter->services)) {
            $base->whereIn('reservations.service_id', $filter->services);
        }
        if (!empty($filter->status)) {
            $base->where('reservations.status', $filter->status);
        }

        // Totals by status
        $totals = (clone $base)
            ->select([
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status IN ('pending','adviser_approved','admin_approved') THEN 1 ELSE 0 END) as pending"),
                DB::raw('COUNT(*) as total'),
            ])->first();

        $total = (int) ($totals->total ?? 0);
        $approved = (int) ($totals->approved ?? 0);
        $rejected = (int) ($totals->rejected ?? 0);
        $cancelled = (int) ($totals->cancelled ?? 0);
        $pending = (int) ($totals->pending ?? 0);

        // Lead time: avg days from created_at to schedule_date for rows with schedule_date
        $lead = (clone $base)
            ->whereNotNull('reservations.schedule_date')
            ->select(DB::raw('AVG(DATEDIFF(reservations.schedule_date, reservations.created_at)) as avg_days'))
            ->first();
        $avgLeadDays = round((float) ($lead->avg_days ?? 0), 2);

        // Day of week distribution
        $dow = (clone $base)
            ->whereNotNull('reservations.schedule_date')
            ->select([
                DB::raw('DAYNAME(reservations.schedule_date) as day'),
                DB::raw('COUNT(*) as cnt'),
            ])
            ->groupBy('day')
            ->orderBy('cnt', 'desc')
            ->get();

        $rows = [
            ['metric' => 'total', 'label' => 'Total Reservations', 'value' => $total, 'description' => 'Total number of reservations processed matching the criteria.'],
            ['metric' => 'approved', 'label' => 'Approved', 'value' => $approved, 'description' => 'Number of reservations that have been approved.'],
            ['metric' => 'rejected', 'label' => 'Rejected', 'value' => $rejected, 'description' => 'Number of reservations that have been rejected.'],
            ['metric' => 'cancelled', 'label' => 'Cancelled', 'value' => $cancelled, 'description' => 'Number of reservations that have been cancelled.'],
            ['metric' => 'pending', 'label' => 'Pending', 'value' => $pending, 'description' => 'Number of reservations currently pending approval.'],
            ['metric' => 'approval_rate_pct', 'label' => 'Approval Rate (%)', 'value' => $total > 0 ? round(($approved / $total) * 100, 2) : 0, 'description' => 'Percentage of total reservations that were approved.'],
            ['metric' => 'cancellation_rate_pct', 'label' => 'Cancellation Rate (%)', 'value' => $total > 0 ? round(($cancelled / $total) * 100, 2) : 0, 'description' => 'Percentage of total reservations that were cancelled.'],
            ['metric' => 'avg_lead_days', 'label' => 'Avg Lead Time (Days)', 'value' => $avgLeadDays, 'description' => 'Average number of days between reservation creation and the scheduled date.'],
        ];

        foreach ($dow as $row) {
            $rows[] = [
                'metric' => 'day_of_week', 
                'label' => (string) $row->day, 
                'value' => (int) $row->cnt,
                'description' => "Number of reservations scheduled on " . (string) $row->day . "s."
            ];
        }

        return $rows;
    }
}

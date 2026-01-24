<?php

namespace App\Services\Reports\Queries;

use App\Models\Reservation;
use App\Models\Organization;
use App\Services\Reports\Filters\ReportFilter;
use Illuminate\Support\Facades\DB;

class PerformanceQuery implements ReportQuery
{
    public function run(ReportFilter $filter): array
    {
        $query = Reservation::query()
            ->select([
                'organizations.org_id',
                'organizations.org_name',
                DB::raw("SUM(CASE WHEN reservations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN reservations.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN reservations.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(DATEDIFF(reservations.schedule_date, reservations.created_at)) as avg_lead_days'),
            ])
            ->join('organizations', 'organizations.org_id', '=', 'reservations.org_id')
            ->groupBy('organizations.org_id', 'organizations.org_name')
            ->orderBy('organizations.org_name');

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
        if (!empty($filter->status)) {
            $query->where('reservations.status', $filter->status);
        }

        $rows = $query->get()->map(function ($row) {
            $total = max((int) $row->total, 1);
            $approved = (int) $row->approved;
            $rejected = (int) $row->rejected;
            $cancelled = (int) $row->cancelled;

            $approvalRate = round(($approved / $total) * 100, 1);
            $cancellationRate = round(($cancelled / $total) * 100, 1);
            $avgLead = round((float) $row->avg_lead_days, 1);
            
            $orgName = (string) $row->org_name;

            // Consolidated "Volume" string
            $volumeStr = "{$total} Total";
            $breakdown = [];
            if ($approved > 0) $breakdown[] = "{$approved} Aprv";
            if ($rejected > 0) $breakdown[] = "{$rejected} Rej";
            if ($cancelled > 0) $breakdown[] = "{$cancelled} Canc";
            
            if (!empty($breakdown)) {
                $volumeStr .= " (" . implode(', ', $breakdown) . ")";
            }

            // Consolidated "Efficiency" string
            $efficiencyStr = "Approval Rate: {$approvalRate}% | Canc. Rate: {$cancellationRate}% | Lead Time: {$avgLead} days";

            return [
                'Organization' => $orgName,
                'Request Volume' => $volumeStr,
                'Efficiency Metrics' => $efficiencyStr,
                'Performance Narrative' => "{$orgName} processed {$total} requests. {$approvalRate}% were approved, averaging {$avgLead} days lead time.",
            ];
        })->toArray();

        return $rows;
    }
}

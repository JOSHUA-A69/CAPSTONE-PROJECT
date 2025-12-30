<?php

namespace App\Services\Reports\Queries;

use InvalidArgumentException;

class QueryFactory
{
    public static function make(string $type): ReportQuery
    {
        return match ($type) {
            'approvals_rejections' => new ApprovalsRejectionsQuery(),
            'booking_summary' => new BookingSummaryQuery(),
            // Alias: reservation_summaries → booking_summary
            'reservation_summaries' => new BookingSummaryQuery(),
            'reservation_statistics' => new ReservationStatisticsQuery(),
            'organization_activities' => new OrganizationActivitiesQuery(),
            // Alias: organization_participation → organization_activities
            'organization_participation' => new OrganizationActivitiesQuery(),
            // Dedicated queries for admin additions
            'service_demand_patterns' => new ServiceDemandPatternsQuery(),
            'officiant_personnel_assignments' => new OfficiantPersonnelAssignmentsQuery(),
            'venue_utilization_availability_trends' => new VenueUtilizationAvailabilityTrendsQuery(),
            // Staff aliases
            'quarterly_statuses' => new QuarterlyReportQuery(),
            'pending_or_cancelled' => new PendingOrCancelledRequestsQuery(),
            'officiants_updated_schedules' => new OfficiantsUpdatedSchedulesQuery(),
            'operations_monitoring' => new OrganizationActivitiesQuery(),
            // Adviser aliases
            'organization_specific_reservations' => new OrganizationSpecificReservationsQuery(),
            'participation_frequency' => new OrganizationActivitiesQuery(),
            'officiant_allocations_by_group' => new OfficiantAllocationsByGroupQuery(),
            'performance' => new PerformanceQuery(),
            'quarterly' => new QuarterlyReportQuery(),
            'mass_reservation' => new MassReservationQuery(),
            'organization_booking_requests' => new OrganizationBookingRequestsQuery(),
            default => throw new InvalidArgumentException("Unsupported report type: {$type}"),
        };
    }
}

interface ReportQuery
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(\App\Services\Reports\Filters\ReportFilter $filter): array;
}

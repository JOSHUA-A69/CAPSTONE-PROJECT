<?php

return [
    'types' => [
        'quarterly' => [
            'label' => 'Quarterly Report',
            'description' => 'Period breakdown of key metrics by quarter.',
        ],
        'approvals_rejections' => [
            'label' => 'Approvals & Rejections',
            'description' => 'Counts and rates of approvals and rejections.',
        ],
        'booking_summary' => [
            'label' => 'Booking Summary',
            'description' => 'Created, confirmed, cancelled bookings with totals.',
        ],
        'reservation_statistics' => [
            'label' => 'Reservation Statistics',
            'description' => 'Utilization, lead time, cancellation rate, peaks.',
        ],
        'organization_activities' => [
            'label' => 'Organization Activities',
            'description' => 'Activity history by organization and adviser.',
        ],
        'performance' => [
            'label' => 'Performance',
            'description' => 'KPIs and trends for an organization/adviser.',
        ],
        'mass_reservation' => [
            'label' => 'Mass Reservation',
            'description' => 'Summaries and audits for bulk reservations.',
        ],
        // Admin-focused additions (mapped to existing queries)
        'reservation_summaries' => [
            'label' => 'Reservation Summaries',
            'description' => 'High-level summaries of reservations over time.',
        ],
        'organization_participation' => [
            'label' => 'Organization-wide Participation',
            'description' => 'Participation metrics aggregated by organization.',
        ],
        'service_demand_patterns' => [
            'label' => 'Service Demand Patterns',
            'description' => 'Demand trends across services and categories.',
        ],
        'officiant_personnel_assignments' => [
            'label' => 'Officiant & personnel assignments',
            'description' => 'Assignments and workloads of officiants/personnel over time.',
        ],
        'venue_utilization_availability_trends' => [
            'label' => 'Venue utilization and availability trends',
            'description' => 'Utilization rates and availability patterns by venue.',
        ],
        // Staff-focused additions (aliases to existing queries)
        'quarterly_statuses' => [
            'label' => 'Quarterly reservation statuses',
            'description' => 'Reservation status breakdown by quarter.',
        ],
        'pending_or_cancelled' => [
            'label' => 'Pending or cancelled requests',
            'description' => 'List and aggregates for pending/cancelled reservations.',
        ],
        'officiants_updated_schedules' => [
            'label' => "Officiants' updated schedules",
            'description' => 'Recent changes to officiants/priest schedules and assignments.',
        ],
        'operations_monitoring' => [
            'label' => 'Operations monitoring reports',
            'description' => 'Operational activity overview for monitoring purposes.',
        ],
        // Adviser-focused additions (aliases to existing queries)
        'organization_specific_reservations' => [
            'label' => 'Organization-specific reservations',
            'description' => 'Reservations scoped to the adviser’s organizations.',
        ],
        'participation_frequency' => [
            'label' => 'Participation frequency',
            'description' => 'Frequency metrics of organization participation in activities.',
        ],
        'officiant_allocations_by_group' => [
            'label' => 'Officiant allocations tied to their group',
            'description' => 'Allocations/assignments of officiants linked to organizations.',
        ],
        // Requests-level reporting
        'organization_booking_requests' => [
            'label' => 'Organization booking requests',
            'description' => 'Submitted requests with status breakdowns and response times.',
        ],
    ],

    // Role → allowed report type keys
    'access' => [
        'admin' => [
            'quarterly',
            'organization_activities',
            'reservation_statistics',
            'mass_reservation',
            // Admin additions
            'reservation_summaries',
            'organization_participation',
            'service_demand_patterns',
            'officiant_personnel_assignments',
            'venue_utilization_availability_trends',
            'organization_booking_requests',
        ],
        'staff' => [
            'quarterly',
            'organization_activities',
            'reservation_statistics',
            'mass_reservation',
            // Staff additions
            'quarterly_statuses',
            'pending_or_cancelled',
            'officiants_updated_schedules',
            'operations_monitoring',
        ],
        'adviser' => [
            'performance',
            'approvals_rejections',
            'booking_summary',
            'quarterly',
            // Adviser additions
            'organization_specific_reservations',
            'participation_frequency',
            'officiant_allocations_by_group',
            'organization_booking_requests',
        ],
    ],

    'export_formats' => ['pdf', 'csv', 'excel'],

    // Optional public path (relative to /public) used as full-width PDF report header banner.
    // Example: public/hnu-report-header.png or public/images/hnu-report-header.png
    'pdf_header_image' => env('REPORTS_PDF_HEADER_IMAGE', 'hnu-report-header.png'),

    // Default threshold to decide async job vs sync generation
    'async_threshold_rows' => 25000,
];

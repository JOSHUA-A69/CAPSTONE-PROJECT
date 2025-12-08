<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\LiturgicalSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    /**
     * Unified admin calendar: all upcoming reservations (excluding cancelled/rejected),
     * all upcoming staff-plotted schedules, and highlight those where admin presides.
     */
    public function index()
    {
        // All upcoming reservations across the system except cancelled/rejected
        $reservations = Reservation::with([
                'service:service_id,service_name,service_category',
                'venue:venue_id,name',
                'user:id,first_name,middle_name,last_name',
                'organization:org_id,org_name',
                'officiant:id,first_name,middle_name,last_name'
            ])
            ->whereDate('schedule_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->orderBy('schedule_date')
            ->get([
                'reservation_id',
                'service_id',
                'venue_id',
                'custom_venue_name',
                'schedule_date',
                DB::raw('TIME(schedule_date) as schedule_time'),
                'status',
                'participants_count',
                'activity_name',
                'purpose',
                'theme',
                'commentator',
                'readers',
                'psalmist',
                'prayer_leader',
                'details',
                'priest_selection_type',
                'external_priest_name',
                'external_priest_contact',
                'officiant_id',
                'user_id',
                'org_id'
            ]);

        // All upcoming staff-plotted schedules
        $schedules = LiturgicalSchedule::with([
                'priest:id,first_name,middle_name,last_name',
                'venue:venue_id,name'
            ])
            ->upcoming()
            ->get([
                'schedule_id',
                'title',
                'event_type',
                'mass_subtype',
                'schedule_date',
                'start_time',
                'end_time',
                'location',
                'venue_id',
                'priest_id',
                'external_priest_name',
                'external_priest_contact',
                'is_public',
                'description'
            ]);

        // All upcoming organization booking requests (pending/approved)
        $orgBookings = \App\Models\OrganizationBookingRequest::with([
                'organization:org_id,org_name',
                'requestor:id,first_name,last_name'
            ])
            ->whereDate('requested_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('requested_date')
            ->get([
                'id',
                'organization_id',
                'requestor_id',
                'activity_name',
                'purpose',
                'activity_details',
                'requested_date',
                'requested_venue',
                'estimated_participants',
                'special_requirements',
                'status'
            ]);

        $adminId = Auth::id();

        return view('admin.calendar.index', compact('reservations', 'schedules', 'orgBookings', 'adminId'));
    }
}

<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\LiturgicalSchedule;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(ReservationNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        // Get organizations where this adviser is assigned
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

        // Get reservations linked to those organizations
        $reservations = Reservation::with(['user', 'service', 'venue', 'organization'])
            ->whereIn('org_id', $adviserOrgs)
            ->whereIn('status', ['pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected'])
            ->orderByRaw("CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'adviser_approved' THEN 2
                ELSE 3
            END")
            ->orderByDesc('created_at')
            ->paginate(20);

        // Get count of unnoticed requests (>24 hours old)
        $unnoticedCount = Reservation::whereIn('org_id', $adviserOrgs)
            ->unnoticedByAdviser()
            ->count();

        return view('adviser.reservations.index', compact('reservations', 'unnoticedCount'));
    }

    public function show($reservation_id)
    {
        // Get organizations where this adviser is assigned
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

        $reservation = Reservation::with(['user', 'service', 'venue', 'organization', 'history'])
            ->whereIn('org_id', $adviserOrgs)
            ->findOrFail($reservation_id);

        return view('adviser.reservations.show', compact('reservation'));
    }

    public function approve(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Verify this adviser is assigned to the reservation's organization
        if (!Auth::user()->organizations->pluck('org_id')->contains($reservation->org_id)) {
            abort(403, 'You are not the adviser for this organization.');
        }

        // Allow approval if pending or approved (adviser can confirm availability later)
        if (!in_array($reservation->status, ['pending', 'approved', 'adviser_approved', 'admin_approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be approved at this time.');
        }

        $remarks = $request->input('remarks') ?? 'Approved by organization adviser';

        $reservation->update([
            'status' => 'adviser_approved',
            'adviser_responded_at' => now(),
            'admin_notified_at' => now(), // Notify admin immediately
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'adviser_approved',
            'remarks' => $remarks,
            'performed_at' => now(),
        ]);

        // Send notifications to requestor and CREaM admin/staff
        $this->notificationService->notifyAdviserApproved($reservation, $remarks ?? '');

        return Redirect::back()
            ->with('status', 'reservation-approved')
            ->with('message', 'Reservation approved. CREaM administrators have been notified.');
    }

    public function reject(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Verify this adviser is assigned to the reservation's organization
        if (!Auth::user()->organizations->pluck('org_id')->contains($reservation->org_id)) {
            abort(403, 'You are not the adviser for this organization.');
        }

        // Allow rejection if not already rejected or cancelled
        if (in_array($reservation->status, ['rejected', 'cancelled'])) {
            return Redirect::back()
                ->with('error', 'This reservation has already been rejected or cancelled.');
        }

        $reason = $request->input('reason');

        $reservation->update([
            'status' => 'rejected',
            'adviser_responded_at' => now(),
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'rejected',
            'remarks' => 'Rejected by adviser: ' . $reason,
            'performed_at' => now(),
        ]);

        // Send rejection notifications
        $this->notificationService->notifyAdviserRejected($reservation, $reason);

        return Redirect::back()
            ->with('status', 'reservation-rejected')
            ->with('message', 'Reservation rejected. The requestor has been notified.');
    }

    /**
     * Adviser calendar: upcoming reservations for adviser's organizations + staff-plotted schedules
     */
    public function calendar()
    {
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

            $reservations = Reservation::with([
                    'service:service_id,service_name,service_category',
                    'venue:venue_id,name',
                    'officiant:id,first_name,middle_name,last_name'
                ])
            ->whereIn('org_id', $adviserOrgs)
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
                    'officiant_id'
                ]);

        // Show all upcoming staff-plotted schedules to adviser
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

        return view('adviser.reservations.calendar', compact('reservations', 'schedules'));
    }
}


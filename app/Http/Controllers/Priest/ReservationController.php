<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

/**
 * Priest Reservation Controller
 *
 * Allows priests to view their assigned services and confirm/decline availability
 */
class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(ReservationNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':priest']);
        $this->notificationService = $notificationService;
    }

    /**
     * Show all reservations assigned to this priest
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $timeFilter = $request->input('time', 'upcoming'); // upcoming or past

        $query = Reservation::with(['user', 'service', 'venue', 'organization'])
            ->forPriest(Auth::id());

        if ($status) {
            $query->where('status', $status);
        }

        // Time filter
        if ($timeFilter === 'upcoming') {
            $query->upcoming();
        } elseif ($timeFilter === 'past') {
            $query->past();
        }

        $reservations = $query->orderBy('schedule_date', $timeFilter === 'past' ? 'desc' : 'asc')
            ->paginate(20)
            ->appends($request->only('status', 'time'));

        // Get counts for dashboard
        $pendingConfirmationCount = Reservation::forPriest(Auth::id())
            ->awaitingPriestConfirmation()
            ->count();

        $upcomingCount = Reservation::forPriest(Auth::id())
            ->upcoming()
            ->where('priest_confirmation', 'confirmed')
            ->count();

        return view('priest.reservations.index', compact('reservations', 'status', 'timeFilter', 'pendingConfirmationCount', 'upcomingCount'));
    }

    /**
     * Show reservation details
     */
    public function show($reservation_id)
    {
        $reservation = Reservation::with([
            'user',
            'service',
            'venue',
            'organization.adviser',
            'history.performedBy'
        ])
            ->where('officiant_id', Auth::id())
            ->findOrFail($reservation_id);

        return view('priest.reservations.show', compact('reservation'));
    }

    /**
     * Confirm availability for assigned service
     */
    public function confirm(Request $request, $reservation_id)
    {
        $reservation = Reservation::where('officiant_id', Auth::id())
            ->findOrFail($reservation_id);

        // Only confirm if status is pending_priest_confirmation or admin_approved (reassignment) and not yet confirmed
        if (!in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation is not ready for confirmation.');
        }

        if ($reservation->priest_confirmation === 'confirmed') {
            return Redirect::back()
                ->with('error', 'You have already confirmed this reservation.');
        }

        // Update confirmation status
        $reservation->update([
            'priest_confirmation' => 'confirmed',
            'priest_confirmed_at' => now(),
            'status' => 'approved', // Final approval status
        ]);

        // Create history
        $remarks = $request->input('remarks', 'Priest confirmed availability');
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'priest_confirmed',
            'remarks' => $remarks,
            'performed_at' => now(),
        ]);

        // TODO: Send notification to requestor, adviser, and admin
        // $this->notificationService->notifyPriestConfirmed($reservation);

        return Redirect::back()
            ->with('status', 'reservation-confirmed')
            ->with('message', 'You have confirmed your availability for this service. All parties have been notified.');
    }

    /**
     * Decline assigned service
     */
    public function decline(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::where('officiant_id', Auth::id())
            ->findOrFail($reservation_id);

        // Only decline if status is pending_priest_confirmation or admin_approved (reassignment) and not yet confirmed
        if (!in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be declined at this stage.');
        }

        if ($reservation->priest_confirmation === 'confirmed') {
            return Redirect::back()
                ->with('error', 'You have already confirmed this reservation. Please contact CREaM admin if you need to cancel.');
        }

        $reason = $request->input('reason');

        // Store the priest ID before clearing officiant_id
        $priestId = Auth::id();

        // Store decline record with reservation details
        \App\Models\PriestDecline::create([
            'reservation_id' => $reservation->reservation_id,
            'priest_id' => $priestId,
            'reason' => $reason,
            'declined_at' => now(),
            'reservation_activity_name' => $reservation->activity_name ?? $reservation->service->service_name,
            'reservation_schedule_date' => $reservation->schedule_date,
            'reservation_venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
        ]);

        // Update confirmation status and revert to staff/admin for reassignment
        $reservation->update([
            'priest_confirmation' => 'declined',
            'priest_confirmed_at' => now(),
            'status' => 'pending_priest_reassignment', // New status for admin to reassign
            'officiant_id' => null, // Remove assignment so another priest can be selected
        ]);

        // Create history
        $reservation->history()->create([
            'performed_by' => $priestId,
            'action' => 'priest_declined',
            'remarks' => 'Priest declined availability. Reason: ' . $reason,
            'performed_at' => now(),
        ]);

        // Send notification to admin and staff for reassignment - pass priest ID
        $this->notificationService->notifyPriestDeclined($reservation, $reason, $priestId);

        return Redirect::route('priest.reservations.index')
            ->with('status', 'reservation-declined')
            ->with('message', 'You have declined this assignment. CREaM administrators have been notified to assign another priest.');
    }

    /**
     * View declined services history
     */
    public function declined()
    {
        $declines = \App\Models\PriestDecline::where('priest_id', Auth::id())
            ->with(['reservation', 'priest'])
            ->orderBy('declined_at', 'desc')
            ->paginate(20);

        return view('priest.reservations.declined', compact('declines'));
    }

    /**
     * View priest's calendar/schedule
     */
    public function calendar()
    {
        // Get all confirmed reservations for this priest
        $reservations = Reservation::with(['service', 'venue', 'organization'])
            ->forPriest(Auth::id())
            ->where('priest_confirmation', 'confirmed')
            ->upcoming()
            ->orderBy('schedule_date')
            ->get();

        // Format for calendar display
        $events = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->reservation_id,
                'title' => $reservation->service->service_name,
                'start' => $reservation->schedule_date->toIso8601String(),
                'venue' => $reservation->venue->name,
                'organization' => $reservation->organization->org_name ?? 'N/A',
                'purpose' => $reservation->purpose,
            ];
        });

        return view('priest.reservations.calendar', compact('events', 'reservations'));
    }
}

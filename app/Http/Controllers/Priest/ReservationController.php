<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Status filter - special handling for pending_priest_confirmation
        if ($status === 'pending_priest_confirmation') {
            // Show all reservations awaiting priest confirmation (admin_approved with pending confirmation)
            $query->awaitingPriestConfirmation();
        } elseif ($status) {
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
        $priestId = Auth::id();

        // Allow priests to view reservations where they are:
        // 1. Currently assigned as officiant (officiant_id = priest_id)
        // 2. Previously declined (has a decline record)
        // 3. Received notification for this reservation
        $reservation = Reservation::with([
            'user',
            'service',
            'venue',
            'organization.adviser',
            'history.performedBy'
        ])
            ->where(function ($query) use ($priestId, $reservation_id) {
                $query->where('officiant_id', $priestId)
                    ->orWhereHas('declines', function ($q) use ($priestId) {
                        $q->where('priest_id', $priestId);
                    })
                    ->orWhereExists(function ($q) use ($priestId, $reservation_id) {
                        $q->select(DB::raw(1))
                            ->from('notifications')
                            ->where('reservation_id', $reservation_id)
                            ->where('user_id', $priestId)
                            ->where('type', 'Assignment');
                    });
            })
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

        // Send notification to admin/staff
        $this->notificationService->notifyPriestConfirmed($reservation, Auth::id());

        return Redirect::back()
            ->with('status', 'reservation-confirmed')
            ->with('message', 'You have confirmed your availability for this service. All parties have been notified.');
    }

    /**
     * Decline assigned service (works for both unconfirmed and confirmed reservations)
     */
    public function decline(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::where('officiant_id', Auth::id())
            ->findOrFail($reservation_id);

        // Check if this is a cancellation of already confirmed reservation
        $isCancellation = ($reservation->priest_confirmation === 'confirmed');

        // Allow decline for: pending_priest_confirmation, admin_approved, OR approved (confirmed)
        if (!in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved', 'approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be declined at this stage.');
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
        $historyAction = $isCancellation ? 'priest_cancelled_confirmation' : 'priest_declined';
        $historyRemarks = $isCancellation
            ? 'Priest cancelled their previously confirmed reservation. Reason: ' . $reason
            : 'Priest declined availability. Reason: ' . $reason;

        $reservation->history()->create([
            'performed_by' => $priestId,
            'action' => $historyAction,
            'remarks' => $historyRemarks,
            'performed_at' => now(),
        ]);

        // Send notification to admin and staff for reassignment - pass priest ID
        if ($isCancellation) {
            $this->notificationService->notifyPriestCancelledConfirmation($reservation, $reason, $priestId);
        } else {
            $this->notificationService->notifyPriestDeclined($reservation, $reason, $priestId);
        }

        $message = $isCancellation
            ? 'You have cancelled your confirmation. CREaM administrators have been notified to assign another priest.'
            : 'You have declined this assignment. CREaM administrators have been notified to assign another priest.';

        return Redirect::route('priest.reservations.index')
            ->with('status', 'reservation-declined')
            ->with('message', $message);
    }

    /**
     * Undo decline - Priest wants to accept the assignment they previously declined
     */
    public function undecline($reservation_id)
    {
        $priestId = Auth::id();

        // Find the reservation
        $reservation = Reservation::findOrFail($reservation_id);

        // Find the decline record
        $decline = \App\Models\PriestDecline::where('reservation_id', $reservation_id)
            ->where('priest_id', $priestId)
            ->latest('declined_at')
            ->first();

        if (!$decline) {
            return Redirect::back()->with('error', 'Decline record not found.');
        }

        // Check if reservation was already reassigned to another priest
        if ($reservation->officiant_id && $reservation->officiant_id != $priestId) {
            return Redirect::route('priest.reservations.declined')
                ->with('error', 'This reservation has already been reassigned to another priest. You cannot undo your decline.');
        }

        // Check if reservation status allows undecline
        if (!in_array($reservation->status, ['pending_priest_reassignment', 'adviser_approved', 'admin_approved'])) {
            return Redirect::route('priest.reservations.declined')
                ->with('error', 'This reservation is no longer available for reassignment (Status: ' . $reservation->status . ').');
        }

        // Restore the priest assignment
        $reservation->update([
            'officiant_id' => $priestId,
            'priest_confirmation' => null, // Reset to allow confirmation
            'priest_confirmed_at' => null,
            'status' => 'pending_priest_confirmation', // Back to awaiting priest confirmation
        ]);

        // Count total declines by this priest for this reservation (for admin awareness)
        $totalDeclines = \App\Models\PriestDecline::where('reservation_id', $reservation_id)
            ->where('priest_id', $priestId)
            ->count();

        // Delete the decline record
        $decline->delete();

        // Create history entry with decline count
        $historyRemarks = 'Priest undid their decline and is now available for this assignment.';
        if ($totalDeclines > 1) {
            $historyRemarks .= ' (This priest has declined this reservation ' . $totalDeclines . ' time(s))';
        }

        $reservation->history()->create([
            'performed_by' => $priestId,
            'action' => 'priest_reassigned',
            'remarks' => $historyRemarks,
            'performed_at' => now(),
        ]);

        // Create notifications for ALL admins and staff
        $admins = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
        $priestName = Auth::user()->first_name . ' ' . Auth::user()->last_name;

        // Add warning if priest has changed mind multiple times
        $indecisionWarning = '';
        if ($totalDeclines > 1) {
            $indecisionWarning = ' ⚠️ (Changed mind ' . $totalDeclines . ' times)';
        }

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => '<strong>' . $priestName . '</strong> restored their previously declined reservation' . $indecisionWarning,
                'type' => 'Update',
                'sent_at' => now(),
                'data' => json_encode([
                    'priest_name' => $priestName,
                    'priest_id' => $priestId,
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'requestor_name' => $reservation->user->first_name . ' ' . $reservation->user->last_name,
                    'venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
                    'action' => 'undecline',
                    'decline_count' => $totalDeclines,
                ]),
            ]);
        }

        // Send email notifications to admin/staff
        $this->notificationService->notifyPriestUndeclined($reservation, $priestId);        return Redirect::route('priest.reservations.show', $reservation_id)
            ->with('status', 'reservation-undeclined')
            ->with('message', 'Success! You have undone your decline. Please confirm your availability for this service.');
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

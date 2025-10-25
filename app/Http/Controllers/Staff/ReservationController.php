<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
// use Illuminate\Support\Str;

class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(ReservationNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $search = request('q');
        $status = request('status');

        $query = Reservation::with(['user', 'service', 'venue', 'organization', 'officiant']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                  })
                  ->orWhereHas('organization', function ($oq) use ($search) {
                      $oq->where('org_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('service', function ($sq) use ($search) {
                      $sq->where('service_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $reservations = $query->orderByDesc('created_at')->paginate(20)->appends(request()->only('q', 'status'));

        $statuses = ['pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected', 'cancelled'];

        // Get unnoticed requests count
        $unnoticedCount = Reservation::unnoticedByAdviser()->count();

        return view('staff.reservations.index', compact('reservations', 'statuses', 'search', 'status', 'unnoticedCount'));
    }

    public function show($reservation_id)
    {
        $reservation = Reservation::with(['user', 'service', 'venue', 'organization', 'officiant', 'history.performedBy'])
            ->findOrFail($reservation_id);

        // Compute available priests using +/- configured window
        $minutes = (int) config('reservations.conflict_minutes', 120);
        $windowStart = (clone $reservation->schedule_date)->subMinutes($minutes);
        $windowEnd = (clone $reservation->schedule_date)->addMinutes($minutes);

        $availablePriests = User::where('role', 'priest')
            ->where('status', 'active')
            ->whereNotExists(function ($q) use ($windowStart, $windowEnd, $reservation_id) {
                $q->from('reservations as r')
                    ->selectRaw('1')
                    ->whereColumn('r.officiant_id', 'users.id')
                    ->whereBetween('r.schedule_date', [$windowStart, $windowEnd])
                    ->whereNotIn('r.status', ['cancelled', 'rejected'])
                    ->where('r.reservation_id', '!=', $reservation_id);
            })
            ->orderBy('first_name')
            ->get();

        return view('staff.reservations.show', compact('reservation', 'availablePriests'));
    }

    /**
     * Send follow-up notification to adviser for unnoticed requests
     */
    public function sendFollowUp($reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Only send follow-up if still pending and older than 24 hours
        if ($reservation->status !== 'pending' || $reservation->created_at->greaterThan(now()->subDay())) {
            return Redirect::back()
                ->with('error', 'This reservation does not require a follow-up at this time.');
        }

        // Mark as followed up
        $reservation->update([
            'staff_followed_up_at' => now(),
        ]);

        // Create history
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'staff_followed_up',
            'remarks' => 'Staff sent follow-up reminder to organization adviser',
            'performed_at' => now(),
        ]);

        // Send follow-up notification
        $this->notificationService->notifyAdviserFollowUp($reservation);

        return Redirect::back()
            ->with('status', 'follow-up-sent')
            ->with('message', 'Follow-up notification sent to organization adviser.');
    }

    /**
     * View all unnoticed requests (>24 hours without adviser response)
     */
    public function unnoticed()
    {
        $reservations = Reservation::with(['user', 'service', 'venue', 'organization.adviser'])
            ->unnoticedByAdviser()
            ->orderBy('created_at')
            ->paginate(20);

        return view('staff.reservations.unnoticed', compact('reservations'));
    }

    public function markContacted(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Ensure status is adviser_approved
        if ($reservation->status !== 'adviser_approved') {
            return Redirect::back()->with('error', 'This reservation cannot be marked as contacted at this stage.');
        }

        // Log history only (no token/confirmation step)
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'contacted_requestor',
            'remarks' => 'Staff contacted requestor to verify details (no confirmation required)',
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'requestor-contacted')
            ->with('message', 'Requestor has been marked as contacted. No confirmation step is required.');
    }

    public function approve(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Requestor confirmation step removed (no gating by requestor_confirmed_at)

        // Check if priest is assigned (should be selected by requestor)
        if (!$reservation->officiant_id) {
            return Redirect::back()->with('error', 'No priest assigned to this reservation.');
        }

        // Update status to pending priest confirmation and notify priest
        $reservation->update([
            'status' => 'pending_priest_confirmation',
            'priest_notified_at' => now(),
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'approved_by_staff',
            'remarks' => $request->input('remarks', 'Approved by staff - priest notified for confirmation'),
            'performed_at' => now(),
        ]);

    // Notify the priest of the assignment/confirmation request
    $this->notificationService->notifyPriestAssigned($reservation);

        return Redirect::back()->with('status', 'reservation-approved')->with('message', 'Reservation approved and priest has been notified.');
    }

    public function notAvailable(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);
        $reason = $request->input('reason');

        $reservation->update([
            'status' => 'rejected',
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'marked_not_available',
            'remarks' => 'Marked as not available by staff: ' . $reason,
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-not-available');
    }

    public function finalize(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Check if event has passed
        if ($reservation->schedule_date->isFuture()) {
            return Redirect::back()->with('error', 'Cannot finalize reservation - event has not occurred yet.');
        }

        $reservation->update(['status' => 'completed']);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'completed',
            'remarks' => $request->input('remarks', 'Reservation finalized by staff after successful event'),
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-completed');
    }

    public function assignPriest(Request $request, $reservation_id)
    {
        $request->validate([
            'officiant_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Allow if status is either adviser_approved or pending_priest_assignment
        if (!in_array($reservation->status, ['adviser_approved', 'pending_priest_assignment'])) {
            return Redirect::back()
                ->with('error', 'This reservation is not ready for priest assignment.');
        }

        // Verify selected user is a priest
        $priest = User::where('id', $request->input('officiant_id'))
            ->where('role', 'priest')
            ->firstOrFail();

        // Check for scheduling conflicts using +/- configured window
        $minutes = (int) config('reservations.conflict_minutes', 120);
        $windowStart = (clone $reservation->schedule_date)->subMinutes($minutes);
        $windowEnd = (clone $reservation->schedule_date)->addMinutes($minutes);

        $conflict = Reservation::where('officiant_id', $priest->id)
            ->whereBetween('schedule_date', [$windowStart, $windowEnd])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where('reservation_id', '!=', $reservation_id)
            ->exists();

        if ($conflict) {
            return Redirect::back()
                ->with('error', 'This priest already has an assignment at this date and time.');
        }

        // Assign priest and update status
        $reservation->update([
            'officiant_id' => $priest->id,
            'status' => 'pending_priest_confirmation',
            'priest_notified_at' => now(),
            'priest_confirmation' => 'pending',
        ]);

        // Create history
        $remarks = $request->input('remarks', 'Priest assigned by staff');
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'priest_assigned',
            'remarks' => $remarks . ' - Assigned to: ' . $priest->full_name,
            'performed_at' => now(),
        ]);

    // Notify priest of assignment
    $this->notificationService->notifyPriestAssigned($reservation);

    return Redirect::back()
            ->with('status', 'priest-assigned')
            ->with('message', 'Priest assigned successfully. Awaiting priest confirmation.');
    }

    public function cancel(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        $reason = $request->input('reason');

        $reservation->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => Auth::id(),
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'cancelled',
            'remarks' => 'Cancelled by staff: ' . $reason,
            'performed_at' => now(),
        ]);

        // Send notifications
        $this->notificationService->notifyCancellation(
            $reservation,
            $reason,
            Auth::user()->full_name
        );

        return Redirect::back()->with('status', 'reservation-cancelled');
    }
}

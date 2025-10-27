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

        // Idempotency guard: if already contacted, do not duplicate history entries
        if (!empty($reservation->contacted_at)) {
            return Redirect::back()
                ->with('status', 'requestor-already-contacted')
                ->with('message', 'Requestor was already marked as contacted.');
        }

        // Stamp contacted_at and log history (no token/confirmation step)
        $reservation->update([
            'contacted_at' => now(),
        ]);

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

        // Policy update: Staff do not assign or notify priests.
        // Staff approval records that details were verified and forwards to Admin for priest assignment.

        // Keep status as adviser_approved and log the action
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'approved_by_staff',
            'remarks' => $request->input('remarks', 'Reviewed by staff and forwarded to Admin for priest assignment'),
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-reviewed')->with('message', 'Details reviewed. Admin will assign the priest based on requestor\'s choice.');
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

    /**
     * Reschedule a reservation (Staff)
     */
    public function reschedule(Request $request, $reservation_id)
    {
        $request->validate([
            'schedule_date' => 'required|date|after:now',
            'remarks' => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::with(['officiant'])->findOrFail($reservation_id);

        if (in_array($reservation->status, ['cancelled', 'rejected', 'completed'])) {
            return Redirect::back()->with('error', 'This reservation cannot be rescheduled.');
        }

        $oldDate = clone $reservation->schedule_date;
        $newDate = \Carbon\Carbon::parse($request->input('schedule_date'));

        // If a priest is assigned, ensure no conflict at the new time
        if ($reservation->officiant_id) {
            $minutes = (int) config('reservations.conflict_minutes', 120);
            $windowStart = (clone $newDate)->subMinutes($minutes);
            $windowEnd = (clone $newDate)->addMinutes($minutes);

            $conflict = Reservation::where('officiant_id', $reservation->officiant_id)
                ->whereBetween('schedule_date', [$windowStart, $windowEnd])
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->where('reservation_id', '!=', $reservation_id)
                ->exists();

            if ($conflict) {
                return Redirect::back()->with('error', 'The assigned priest has a conflict at the new schedule. Choose another time or reassign the priest.');
            }
        }

        // Update schedule and reset priest confirmation if assigned
        $reservation->schedule_date = $newDate;
        if ($reservation->officiant_id) {
            $reservation->priest_confirmation = 'pending';
            $reservation->status = 'pending_priest_confirmation';
            $reservation->priest_notified_at = now();
        }
        $reservation->save();

        // History
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'status_updated',
            'remarks' => 'Rescheduled to ' . $newDate->format('M d, Y h:i A') . '. ' . ($request->input('remarks') ?? ''),
            'performed_at' => now(),
        ]);

    // Notifications
    $remarks = (string) ($request->input('remarks') ?? '');
    $this->notificationService->notifyReservationRescheduled($reservation, $oldDate, $remarks);
        if ($reservation->officiant_id) {
            // Re-notify priest to confirm
            $this->notificationService->notifyPriestAssigned($reservation);
        }

        return Redirect::back()->with('status', 'reservation-rescheduled')->with('message', 'Reservation rescheduled successfully.');
    }

    public function assignPriest(Request $request, $reservation_id)
    {
        // Policy: Staff cannot assign or reassign priests. Admin handles assignment based on requestor's choice.
        return Redirect::back()->with('error', 'Policy update: Staff cannot assign priests. Please coordinate with the Admin for priest assignment.');
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

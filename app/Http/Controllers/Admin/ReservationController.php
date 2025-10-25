<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

/**
 * Admin Reservation Controller
 *
 * Handles final approval and priest assignment in the reservation workflow.
 * This is the CREaM Administrator's main interface for managing reservations.
 */
class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(ReservationNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display all reservations with filtering
     */
    public function index(Request $request)
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $query = Reservation::with(['user', 'service', 'venue', 'organization', 'officiant']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                  })
                  ->orWhereHas('organization', function ($orgQuery) use ($search) {
                      $orgQuery->where('org_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('service', function ($svcQuery) use ($search) {
                      $svcQuery->where('service_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

    // Priority: adviser_approved > pending_priest_confirmation/admin_approved > pending > others
        $reservations = $query->orderByRaw("CASE
                WHEN status = 'adviser_approved' THEN 1
        WHEN status = 'pending_priest_confirmation' THEN 2
        WHEN status = 'admin_approved' THEN 3
                WHEN status = 'pending' THEN 3
                ELSE 4
            END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->only('q', 'status'));

    $statuses = ['pending', 'adviser_approved', 'pending_priest_assignment', 'pending_priest_confirmation', 'pending_priest_reassignment', 'admin_approved', 'approved', 'rejected', 'cancelled'];

        // Count pending admin approval
        $pendingCount = Reservation::where('status', 'adviser_approved')->count();

        return view('admin.reservations.index', compact('reservations', 'statuses', 'search', 'status', 'pendingCount'));
    }

    /**
     * Show reservation details with priest assignment interface
     */
    public function show($reservation_id)
    {
        $reservation = Reservation::with([
            'user',
            'service',
            'venue',
            'organization.adviser',
            'officiant',
            'history.performedBy',
            'cancelledByUser'
        ])->findOrFail($reservation_id);

        // Get available priests (not conflicting with this schedule)
        $availablePriests = $this->getAvailablePriests($reservation->schedule_date, $reservation_id);

        // Mark any unread notifications for this reservation as read
        \App\Models\Notification::where('user_id', Auth::id())
            ->where('reservation_id', $reservation_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.reservations.show', compact('reservation', 'availablePriests'));
    }

    /**
     * Assign priest to reservation (Admin approves + assigns officiant)
     */
    public function assignPriest(Request $request, $reservation_id)
    {
        $request->validate([
            'officiant_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Allow if status is adviser_approved OR pending_priest_assignment OR pending_priest_reassignment (reassignment)
        if (!in_array($reservation->status, ['adviser_approved', 'pending_priest_assignment', 'pending_priest_reassignment'])) {
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

        // Determine if this is a reassignment or initial assignment
        $isReassignment = in_array($reservation->status, ['priest_declined', 'pending_priest_reassignment']);

        // Assign priest and update status
        $reservation->update([
            'officiant_id' => $priest->id,
            'status' => 'admin_approved',
            'priest_notified_at' => now(),
            'priest_confirmation' => 'pending',
        ]);

        // Create history
        $remarks = $request->input('remarks', $isReassignment ? 'Priest reassigned after decline' : 'Priest assigned by admin');
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => $isReassignment ? 'priest_reassigned' : 'priest_assigned',
            'remarks' => $remarks . ' - Assigned to: ' . $priest->full_name,
            'performed_at' => now(),
        ]);

        // Send notifications to priest and requestor
        $this->notificationService->notifyPriestAssigned($reservation);

        return Redirect::back()
            ->with('status', 'priest-assigned')
            ->with('message', 'Priest assigned successfully. Awaiting priest confirmation.');
    }

    /**
     * Reject a reservation (Admin-level rejection)
     */
    public function reject(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        if (in_array($reservation->status, ['cancelled', 'rejected', 'approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be rejected.');
        }

        $reason = $request->input('reason');

        $reservation->update([
            'status' => 'rejected',
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'rejected',
            'remarks' => 'Rejected by admin: ' . $reason,
            'performed_at' => now(),
        ]);

        // Send notifications
        $this->notificationService->notifyAdviserRejected($reservation, $reason);

        return Redirect::back()
            ->with('status', 'reservation-rejected')
            ->with('message', 'Reservation rejected. Requestor has been notified.');
    }

    /**
     * Get available priests for a specific date/time
     */
    private function getAvailablePriests($scheduleDate, $excludeReservationId = null)
    {
        $minutes = (int) config('reservations.conflict_minutes', 120);
        $windowStart = (clone $scheduleDate)->subMinutes($minutes);
        $windowEnd = (clone $scheduleDate)->addMinutes($minutes);

        // Return only priests with no conflicting reservations within the time window
        return User::where('role', 'priest')
            ->where('status', 'active')
            ->whereNotExists(function ($q) use ($windowStart, $windowEnd, $excludeReservationId) {
                $q->from('reservations as r')
                    ->selectRaw('1')
                    ->whereColumn('r.officiant_id', 'users.id')
                    ->whereBetween('r.schedule_date', [$windowStart, $windowEnd])
                    ->whereNotIn('r.status', ['cancelled', 'rejected']);
                if ($excludeReservationId) {
                    $q->where('r.reservation_id', '!=', $excludeReservationId);
                }
            })
            ->orderBy('first_name')
            ->get();
    }
}

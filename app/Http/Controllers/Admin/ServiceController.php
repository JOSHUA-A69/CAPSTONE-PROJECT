<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin Service Controller
 *
 * Handles admin's own service assignments when they are assigned as a priest
 */
class ServiceController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(ReservationNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
        $this->notificationService = $notificationService;
    }

    /**
     * Show all service assignments for the admin (as priest)
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $timeFilter = $request->input('time', 'upcoming');

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

        // Get counts
        $pendingConfirmationCount = Reservation::forPriest(Auth::id())
            ->awaitingPriestConfirmation()
            ->count();

        $upcomingCount = Reservation::forPriest(Auth::id())
            ->upcoming()
            ->where('priest_confirmation', 'confirmed')
            ->count();

        return view('admin.services.index', compact('reservations', 'status', 'timeFilter', 'pendingConfirmationCount', 'upcomingCount'));
    }

    /**
     * Show detailed view of a service assignment
     */
    public function show($reservation_id)
    {
        $adminId = Auth::id();

        $reservation = Reservation::with([
            'user',
            'service',
            'venue',
            'organization.adviser',
            'history.performedBy'
        ])
            ->where(function ($query) use ($adminId, $reservation_id) {
                $query->where('officiant_id', $adminId)
                    ->orWhereHas('declines', function ($q) use ($adminId) {
                        $q->where('priest_id', $adminId);
                    });
            })
            ->findOrFail($reservation_id);

        return view('admin.services.show', compact('reservation'));
    }

    /**
     * Confirm admin's availability for a service
     *
     * When admin confirms, we skip self-notification but notify requestor and adviser
     */
    public function confirm($reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Verify admin is the assigned priest
        if ($reservation->officiant_id != Auth::id()) {
            return redirect()->back()->with('error', 'You are not assigned to this reservation.');
        }

        // Check if already confirmed
        if ($reservation->priest_confirmation === 'confirmed') {
            return redirect()->route('admin.services.show', $reservation_id)
                ->with('info', 'This service has already been confirmed.');
        }

        DB::beginTransaction();
        try {
            // Update priest confirmation
            $reservation->priest_confirmation = 'confirmed';
            $reservation->priest_confirmed_at = now();

            // Update status to confirmed (final step)
            $reservation->status = 'confirmed';
            $reservation->save();

            // Add history record (use enum-safe action)
            $reservation->history()->create([
                'performed_by' => Auth::id(),
                'action' => 'priest_confirmed',
                'remarks' => 'Admin confirmed their own availability for this service',
                'performed_at' => now(),
            ]);

            // Send notifications (NO self-notification to admin)
            // Notify requestor
            $this->notificationService->notifyRequestorPriestConfirmed($reservation, Auth::user());

            // If there's an adviser, notify them too
            if ($reservation->organization && $reservation->organization->adviser) {
                $this->notificationService->notifyAdviserPriestConfirmed($reservation, Auth::user());
            }

            // Notify OTHER admin/staff members (the notifyPriestConfirmed method now excludes self)
            $this->notificationService->notifyPriestConfirmed($reservation, Auth::id());

            DB::commit();

            $message = 'Reservation approved successfully. The requestor has been notified.';
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('admin.services.show', $reservation_id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin service confirmation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to confirm service: ' . $e->getMessage());
        }
    }

    /**
     * Decline a service assignment and reassign to another priest
     *
     * When admin declines, NO self-notification is sent
     * Only notify the newly assigned priest, requestor, and adviser
     */
    public function decline(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Verify admin is the assigned priest
        if ($reservation->officiant_id != Auth::id()) {
            return redirect()->back()->with('error', 'You are not assigned to this reservation.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
            'new_priest_id' => 'required|exists:users,id'
        ]);

        $newPriestId = $request->input('new_priest_id');
        $reason = $request->input('reason', 'Schedule conflict');

        // Verify new priest exists and has priest role
        $newPriest = User::findOrFail($newPriestId);
        if (!in_array($newPriest->role, ['priest', 'admin'])) {
            return redirect()->back()->with('error', 'Selected user is not a priest.');
        }

        DB::beginTransaction();
        try {
            $oldPriestId = $reservation->officiant_id;

            // Record the decline
            $reservation->declines()->create([
                'priest_id' => Auth::id(),
                'reason' => $reason,
                'declined_at' => now()
            ]);

            // Assign new priest
            $reservation->officiant_id = $newPriestId;
            $reservation->priest_confirmation = 'pending';
            $reservation->priest_confirmed_at = null;
            $reservation->status = 'pending_priest_confirmation';
            $reservation->save();

            // Add history (enum-safe action)
            $reservation->history()->create([
                'performed_by' => Auth::id(),
                'action' => 'priest_reassigned',
                'remarks' => "Admin declined and assigned to {$newPriest->full_name}. Reason: {$reason}",
                'performed_at' => now(),
            ]);

            // Send notifications (NO self-notification to admin)
            // Notify NEW priest about assignment
            $this->notificationService->notifyPriestAssignment($reservation, $newPriest);

            // Notify requestor about priest change
            $this->notificationService->notifyRequestorPriestReassigned($reservation, Auth::user(), $newPriest);

            // If there's an adviser, notify them
            if ($reservation->organization && $reservation->organization->adviser) {
                $this->notificationService->notifyAdviserPriestReassigned($reservation, Auth::user(), $newPriest);
            }

            DB::commit();

            Log::info('Admin declined and reassigned service', [
                'admin_id' => Auth::id(),
                'reservation_id' => $reservation_id,
                'new_priest_id' => $newPriestId,
                'reason' => $reason,
            ]);

            $message = 'Reservation rejected successfully. The requestor has been notified.';
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('admin.services.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin service decline failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to decline and reassign: ' . $e->getMessage());
        }
    }    /**
     * Show calendar view of admin's services
     */
    public function calendar()
    {
        $reservations = Reservation::forPriest(Auth::id())
            ->whereIn('status', ['pending_priest_confirmation', 'confirmed'])
            ->get();

        $events = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->reservation_id,
                'title' => $reservation->activity_name,
                'start' => $reservation->schedule_date . 'T' . $reservation->schedule_time,
                'backgroundColor' => $reservation->priest_confirmation === 'confirmed' ? '#10b981' : '#f59e0b',
                'borderColor' => $reservation->priest_confirmation === 'confirmed' ? '#059669' : '#d97706',
                'url' => route('admin.services.show', $reservation->reservation_id)
            ];
        });

        return view('admin.services.calendar', compact('events'));
    }

    /**
     * Show declined services
     */
    public function declined()
    {
        $reservations = Reservation::with(['user', 'service', 'venue', 'organization'])
            ->whereHas('declines', function ($query) {
                $query->where('priest_id', Auth::id());
            })
            ->orderBy('schedule_date', 'desc')
            ->paginate(20);

        return view('admin.services.declined', compact('reservations'));
    }
}

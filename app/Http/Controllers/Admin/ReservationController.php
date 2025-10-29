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
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

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
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Priority: adviser_approved > admin_approved > pending > others
        $reservations = $query->orderByRaw("CASE
                WHEN status = 'adviser_approved' THEN 1
                WHEN status = 'admin_approved' THEN 2
                WHEN status = 'pending' THEN 3
                ELSE 4
            END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends($request->only('q', 'status'));

        $statuses = ['pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected', 'cancelled'];

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

        // Allow if status is adviser_approved OR priest_declined OR pending_priest_reassignment (reassignment)
        if (!in_array($reservation->status, ['adviser_approved', 'priest_declined', 'pending_priest_reassignment'])) {
            return Redirect::back()
                ->with('error', 'This reservation is not ready for priest assignment.');
        }

        // Verify selected user is a priest
        $priest = User::where('id', $request->input('officiant_id'))
            ->where('role', 'priest')
            ->firstOrFail();

        // Check for scheduling conflicts
        $conflict = Reservation::where('officiant_id', $priest->id)
            ->where('schedule_date', $reservation->schedule_date)
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
            'action' => $isReassignment ? 'priest_reassigned' : 'admin_approved',
            'remarks' => $remarks . ' - Assigned to: ' . $priest->full_name,
            'performed_at' => now(),
        ]);

        // Send notifications to priest and requestor
        $this->notificationService->notifyPriestAssigned($reservation);

        $message = 'Reservation approved successfully. The requestor has been notified.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return Redirect::back()
            ->with('status', 'priest-assigned')
            ->with('message', $message);
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

        $message = 'Reservation rejected successfully. The requestor has been notified.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return Redirect::back()
            ->with('status', 'reservation-rejected')
            ->with('message', $message);
    }

    /**
     * Confirm external priest reservation (Admin approves external priest)
     */
    public function confirmExternal(Request $request, $reservation_id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Verify this is an external priest reservation
        if ($reservation->priest_selection_type !== 'external') {
            return Redirect::back()
                ->with('error', 'This reservation does not have an external priest.');
        }

        // Allow if status is pending or adviser_approved
        if (!in_array($reservation->status, ['pending', 'adviser_approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be confirmed at this stage.');
        }

        // Update reservation status to admin_approved (since external priest doesn't need further confirmation)
        $reservation->update([
            'status' => 'admin_approved',
        ]);

        // Create history
        $notes = $request->input('admin_notes', '');
        $remarks = 'External priest reservation confirmed by admin';
        if (!empty($notes)) {
            $remarks .= ' - Notes: ' . $notes;
        }
        $remarks .= ' - External Priest: ' . $reservation->external_priest_name;

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'admin_approved',
            'remarks' => $remarks,
            'performed_at' => now(),
        ]);

        // Send notification to requestor
        try {
            $this->notifyExternalPriestConfirmed($reservation);
        } catch (\Exception $e) {
            Log::error('Failed to send external priest confirmation notification: ' . $e->getMessage());
        }

        $message = 'External priest reservation confirmed successfully. The requestor has been notified.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return Redirect::back()
            ->with('status', 'external-priest-confirmed')
            ->with('message', $message);
    }

    /**
     * Send notification when admin confirms external priest reservation
     */
    private function notifyExternalPriestConfirmed(Reservation $reservation): void
    {
        // In-app notification to requestor
        try {
            $message = "Your reservation for <strong>{$reservation->service->service_name}</strong> has been approved by the admin. Your reservation with {$reservation->external_priest_name} is confirmed for " . $reservation->schedule_date->format('M d, Y h:i A');
            
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];
            
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'external_priest_name' => $reservation->external_priest_name,
                    'action' => 'external_priest_confirmed',
                ]);
            }
            
            \App\Models\Notification::create($notificationData);
            Log::info('External priest confirmation notification sent to requestor (ID: ' . $reservation->user_id . ')');
        } catch (\Exception $e) {
            Log::error('Failed to create external priest confirmation notification: ' . $e->getMessage());
        }

        // In-app notification to adviser
        if ($reservation->organization && $reservation->organization->adviser) {
            try {
                $adviser = $reservation->organization->adviser;
                $message = "Reservation for <strong>{$reservation->service->service_name}</strong> with external priest has been confirmed by admin.";
                
                $notificationData = [
                    'user_id' => $adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Update',
                    'sent_at' => now(),
                ];
                
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'service_name' => $reservation->service->service_name,
                        'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                        'action' => 'external_priest_confirmed',
                    ]);
                }
                
                \App\Models\Notification::create($notificationData);
                Log::info('External priest confirmation notification sent to adviser (ID: ' . $adviser->id . ')');
            } catch (\Exception $e) {
                Log::error('Failed to create adviser notification for external priest confirmation: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get available priests for a specific date/time
     */
    private function getAvailablePriests($scheduleDate, $excludeReservationId = null)
    {
        // Get all priests
        $allPriests = User::where('role', 'priest')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        // Get priests already assigned at this time
        $assignedPriestIds = Reservation::where('schedule_date', $scheduleDate)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->when($excludeReservationId, function ($q) use ($excludeReservationId) {
                $q->where('reservation_id', '!=', $excludeReservationId);
            })
            ->pluck('officiant_id')
            ->toArray();

        // Mark availability
        return $allPriests->map(function ($priest) use ($assignedPriestIds) {
            $priest->is_available = !in_array($priest->id, $assignedPriestIds);
            return $priest;
        });
    }
}

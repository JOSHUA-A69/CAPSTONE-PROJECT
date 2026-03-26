<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationNotificationService;
use App\Services\AvailabilityService;
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
    protected AvailabilityService $availabilityService;

    public function __construct(ReservationNotificationService $notificationService, AvailabilityService $availabilityService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
        $this->notificationService = $notificationService;
        $this->availabilityService = $availabilityService;
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
            'priests',
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
        $reservation = Reservation::findOrFail($reservation_id);
        $authUser = Auth::user();

        // Self-approval shortcut: if the authenticated admin is already the selected officiant
        // and the reservation is awaiting admin approval, allow a single step approve + confirm.
        if (!empty($reservation->officiant_id)
            && $reservation->officiant_id === $authUser->id
            && in_array($reservation->status, ['adviser_approved', 'pending'])
        ) {
            // Wrap in transaction for consistency
            DB::beginTransaction();
            try {
                // Update reservation to approved & confirmed
                $reservation->update([
                    'status' => 'approved', // final workable status (ENUM-safe)
                    'priest_confirmation' => 'confirmed',
                    'priest_confirmed_at' => now(),
                    'approved_by' => $authUser->id,
                ]);

                // History: admin_approved + priest_confirmed (two discrete entries for audit clarity)
                $reservation->history()->create([
                    'performed_by' => $authUser->id,
                    'action' => 'admin_approved',
                    'remarks' => 'Admin (also assigned priest) approved reservation. (Self-approval fast path)',
                    'performed_at' => now(),
                ]);
                $reservation->history()->create([
                    'performed_by' => $authUser->id,
                    'action' => 'priest_confirmed',
                    'remarks' => 'Admin (as priest) confirmed availability in self-approval step.',
                    'performed_at' => now(),
                ]);

                // Refresh relations for notifications
                $reservation->load(['user','service','organization.adviser']);

                // Notify requestor & adviser and other admins/staff (exclude self)
                try {
                    $this->notificationService->notifyRequestorPriestConfirmed($reservation, $authUser);
                } catch (\Throwable $e) { Log::warning('Self-approval notifyRequestor failed: '.$e->getMessage()); }
                if ($reservation->organization && $reservation->organization->adviser) {
                    try { $this->notificationService->notifyAdviserPriestConfirmed($reservation, $authUser); } catch (\Throwable $e) { Log::warning('Self-approval notifyAdviser failed: '.$e->getMessage()); }
                }
                try { $this->notificationService->notifyPriestConfirmed($reservation, $authUser->id); } catch (\Throwable $e) { Log::warning('Self-approval notifyPriestConfirmed failed: '.$e->getMessage()); }

                DB::commit();

                $message = 'Reservation approved and your availability confirmed.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }
                return Redirect::route('admin.reservations.show', $reservation_id)
                    ->with('status', 'priest-self-approved')
                    ->with('message', $message);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Self-approval fast path failed: '.$e->getMessage());
                return Redirect::back()->with('error', 'Failed to self-approve: '.$e->getMessage());
            }
        }

        // If the authenticated admin is already the assigned priest but not in allowed status, block reassignment
        // REMOVED: Allow admin to reassign even if they are the current officiant (e.g. to decline/delegate)
        /*
        if (!empty($reservation->officiant_id) && $reservation->officiant_id === $authUser->id) {
            return Redirect::back()
                ->withErrors(['officiant_id' => 'You are the assigned priest and cannot reassign another priest.']);
        }
        */

        // Proceed with standard assignment flow (requires selecting a priest)
        $request->validate([
            'officiant_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string|max:500',
        ]);

            // Prevent assigning an internal priest when the requestor selected an external priest
            if ($reservation->priest_selection_type === 'external') {
                return back()->withErrors(['officiant_id' => 'This reservation uses an external priest. You cannot assign an internal priest. Use "Confirm External Priest" instead.']);
            }

        // Allow if status is adviser_approved OR priest_declined OR pending_priest_reassignment (reassignment)
        if (!in_array($reservation->status, ['adviser_approved', 'priest_declined', 'pending_priest_reassignment'])) {
            return Redirect::back()
                ->with('error', 'This reservation is not ready for priest assignment.');
        }

        // Verify selected user is a priest
        $priest = User::where('id', $request->input('officiant_id'))
            ->where('role', 'priest')
            ->firstOrFail();

        // Check for scheduling conflicts using AvailabilityService for proper 2-hour block checking
        $availabilityCheck = $this->availabilityService->isPriestAvailable(
            $priest->id,
            $reservation->schedule_date,
            $reservation_id
        );

        if (!$availabilityCheck['available']) {
            return Redirect::back()
                ->with('error', $availabilityCheck['message']);
        }

        // Determine if this is a reassignment or initial assignment
        $isReassignment = in_array($reservation->status, ['priest_declined', 'pending_priest_reassignment']);

        // Assign priest and update status
        $reservation->update([
            'officiant_id' => $priest->id,
            'status' => 'admin_approved',
            'priest_notified_at' => now(),
            'priest_confirmation' => 'pending',
            'approved_by' => Auth::id(),
        ]);

        // Sync to pivot table to ensure consistency (removes old priest, adds new one)
        $reservation->priests()->sync([
            $priest->id => [
                'confirmation_status' => 'pending',
                'notified' => true,
                'notified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
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

        $priestName = 'Fr. ' . $priest->first_name . ' ' . $priest->last_name;
        $message = "Successfully assigned {$priestName} to this reservation. The priest and requestor have been notified.";
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

        $reason = trim(strip_tags((string) $request->input('reason')));

        $reservation->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'rejected',
            'remarks' => 'Rejected by admin: ' . $reason,
            'performed_at' => now(),
        ]);

    // Send notifications with correct admin wording
    $this->notificationService->notifyAdminRejected($reservation, $reason, Auth::user());

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

        // External priest doesn't need further confirmation → mark fully approved
        $reservation->update([
            'status' => 'approved',
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
            $message = "Your reservation for <strong>" . ($reservation->service?->service_name ?? 'Unknown Service') . "</strong> has been approved by the admin. Your reservation with {$reservation->external_priest_name} is confirmed for " . $reservation->schedule_date->format('M d, Y h:i A');
            
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];
            
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
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
                $message = "Reservation for <strong>" . ($reservation->service?->service_name ?? 'Unknown Service') . "</strong> with external priest has been confirmed by admin.";
                
                $notificationData = [
                    'user_id' => $adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Update',
                    'sent_at' => now(),
                ];
                
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
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
     * Final approval after all priests have confirmed
     * This is the admin's final step to mark the reservation as fully approved
     */
    public function finalApprove(Request $request, $reservation_id)
    {
        $reservation = Reservation::with(['priests', 'user', 'service', 'organization'])->findOrFail($reservation_id);

        // Allow approval if admin_approved OR adviser_approved (handling manual overrides or sync issues)
        if (!in_array($reservation->status, ['admin_approved', 'adviser_approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation is not ready for final approval. Current status: ' . $reservation->status);
        }

        // Warning instead of blocker: Check confirmation but allow Admin to override if they choose to
        // We will assume if Admin clicks "Final Approve", they are overriding any missing confirmations.
        // if (!$reservation->allPriestsConfirmed() && $reservation->priest_confirmation !== 'confirmed') { ... }

        DB::beginTransaction();
        try {
            $remarks = $request->input('remarks', 'Final approval by admin');

            $reservation->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]);

            $reservation->history()->create([
                'performed_by' => Auth::id(),
                'action' => 'admin_approved',
                'remarks' => $remarks,
                'performed_at' => now(),
            ]);

            // Notify requestor about final approval
            $this->notificationService->notifyFinalApproval($reservation);

            DB::commit();

            $message = 'Reservation has been finally approved. The requestor has been notified.';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return Redirect::back()
                ->with('status', 'reservation-approved')
                ->with('message', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Final approval failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to approve reservation: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a reservation (Admin-initiated)
     */
    public function cancel(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Disallow if already terminal state
        if (in_array($reservation->status, ['cancelled', 'rejected'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be cancelled as it is already ' . $reservation->status . '.');
        }

        $reason = trim(strip_tags((string) $request->input('reason')));

        // Update reservation status and audit fields
        $reservation->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => Auth::id(),
        ]);

        // History entry
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'cancelled',
            'remarks' => 'Cancelled by admin: ' . $reason,
            'performed_at' => now(),
        ]);

        // Notifications (email/SMS/in-app)
        $this->notificationService->notifyCancellation(
            $reservation->fresh(['user','service','organization.adviser','officiant']),
            $reason,
            Auth::user()->full_name
        );

        return Redirect::back()
            ->with('status', 'reservation-cancelled')
            ->with('message', 'Reservation cancelled and all parties have been notified.');
    }

    /**
     * Decline assignment as priest (when Admin is the assigned priest)
     */
    public function declineAssignment(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'replacement_priest_id' => 'nullable|exists:users,id',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);
        $priestId = Auth::id();

        // Verify the authenticated user (admin) is actually the assigned priest
        $isOfficiant = $reservation->officiant_id === $priestId;
        $isAssignedInPivot = $reservation->priests()->where('users.id', $priestId)->exists();
             
        if (!$isOfficiant && !$isAssignedInPivot) {
            return Redirect::back()
                ->with('error', 'You are not assigned to this reservation.');
        }

        DB::beginTransaction();
        try {
            $reason = trim(strip_tags((string) $request->input('reason')));
            $replacementId = $request->input('replacement_priest_id');

            // 1. Create PriestDecline record
            \App\Models\PriestDecline::create([
                'reservation_id' => $reservation->reservation_id,
                'priest_id' => $priestId,
                'reason' => $reason,
                'declined_at' => now(),
                'reservation_activity_name' => $reservation->activity_name ?? $reservation->service?->service_name ?? 'Unknown Service',
                'reservation_schedule_date' => $reservation->schedule_date,
                'reservation_venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
            ]);

            // 2. Update pivot status if exists
            if ($isAssignedInPivot) {
                $reservation->priests()->updateExistingPivot($priestId, [
                    'confirmation_status' => 'declined',
                    'decline_reason' => $reason,
                    'responded_at' => now(),
                ]);
            }

            // 3. Handle Replacement or Default Decline
            if ($replacementId) {
                $newPriest = User::find($replacementId);

                // Update reservation to new priest immediately
                $reservation->update([
                    'officiant_id' => $newPriest->id,
                    'status' => 'admin_approved',
                    'priest_confirmation' => 'pending',
                    'priest_notified_at' => now(),
                    'approved_by' => Auth::id(), // Re-approve as admin
                ]);

                // Sync pivot for new priest (removing old one implicity or explicitly)
                // We use sync to ensure dirty state is cleared, but be careful not to remove other priests if multiple assigned
                // For now, assuming single replacement flow for officiant
                 $reservation->priests()->syncWithoutDetaching([
                    $newPriest->id => [
                        'confirmation_status' => 'pending',
                        'notified' => true,
                        'notified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
                
                // Remove self from pivot if needed, or leave as declined? 
                // Usually better to leave as declined record in pivot, but syncWithoutDetaching keeps it.
                // But we updated pivot to 'declined' earlier.

                // History
                $reservation->history()->create([
                    'performed_by' => $priestId,
                    'action' => 'priest_declined',
                    'remarks' => "Admin declined and reassigned to Fr. {$newPriest->last_name}. Reason: {$reason}",
                    'performed_at' => now(),
                ]);

                // Notify new priest
                $this->notificationService->notifyPriestAssigned($reservation);

                $message = "You have declined the assignment and successfully reassigned it to Fr. {$newPriest->last_name}.";
            } else {
                // No replacement selected yet
                $reservation->update([
                    'status' => 'priest_declined',
                    'priest_confirmation' => 'declined',
                ]);

                // History
                $reservation->history()->create([
                    'performed_by' => $priestId,
                    'action' => 'priest_declined',
                    'remarks' => 'Admin (as Priest) declined assignment: ' . $reason,
                    'performed_at' => now(),
                ]);

                $message = 'You have declined the assignment. Please assign a replacement priest when ready.';
            }

            // 5. Notify parties (generic decline notification only if no immediate replacement?)
            // If replaced immediately, we notified the NEW priest above. Requestor sees change in logs.
            // If NO replacement, proceed with old notification logic
            if (!$replacementId) {
                try {
                    $this->notificationService->notifyPriestDeclined($reservation, $reason, Auth::id()); 
                } catch (\Exception $e) {
                    Log::warning('Notification failed during admin decline: ' . $e->getMessage());
                }
            }

            DB::commit();

            return Redirect::back()
                ->with('status', 'assignment-declined')
                ->with('message', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin decline assignment failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to decline: ' . $e->getMessage());
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

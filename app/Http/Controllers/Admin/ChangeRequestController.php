<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationChange;
use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Notifications as NotificationHelper;

class ChangeRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    /**
     * Display list of all pending change requests
     */
    public function index()
    {
        $changeRequests = ReservationChange::with(['reservation.service', 'reservation.venue', 'requestor'])
            ->orderBy('status', 'asc') // pending first
            ->orderBy('requested_at', 'desc')
            ->paginate(20);

        return view('admin.change-requests.index', compact('changeRequests'));
    }

    /**
     * Display a specific change request with details
     */
    public function show($id)
    {
        $changeRequest = ReservationChange::with([
            'reservation.service',
            'reservation.venue',
            'reservation.organization.adviser',
            'reservation.officiant',
            'requestor',
            'reviewer'
        ])->findOrFail($id);

        return view('admin.change-requests.show', compact('changeRequest'));
    }

    /**
     * Approve a change request and apply changes to reservation
     */
    public function approve(Request $request, $id)
    {
        $changeRequest = ReservationChange::with('reservation', 'requestor')->findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'This change request has already been ' . $changeRequest->status);
        }

        DB::transaction(function () use ($changeRequest, $request) {
            // Apply the changes to the reservation
            $changes = $changeRequest->changes_requested;
            $reservation = $changeRequest->reservation;

            foreach ($changes as $field => $change) {
                if (isset($change['new'])) {
                    $reservation->$field = $change['new'];
                }
            }
            $reservation->save();

            // Update change request status
            $changeRequest->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Create history record
            $reservation->history()->create([
                'performed_by' => Auth::id(),
                'action' => 'changes_approved',
                'remarks' => 'Admin approved changes requested by ' . $changeRequest->requestor->full_name,
                'performed_at' => now(),
            ]);

            // Notify requestor that changes were approved
            NotificationHelper::make([
                'user_id' => $changeRequest->requested_by,
                'type' => NotificationHelper::TYPE_EDIT_APPROVED,
                'title' => 'Reservation Changes Approved',
                'message' => 'Your requested changes to reservation #' . $reservation->reservation_id . ' have been approved and applied.',
                'reservation_id' => $reservation->reservation_id,
                'data' => [
                    'action' => 'changes_approved',
                    'approved_by' => Auth::user()->full_name,
                ],
                'is_read' => false,
            ]);
        });

        return redirect()->route('admin.change-requests.index')
            ->with('status', 'Change request approved successfully');
    }

    /**
     * Reject a change request with reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ]);

        $changeRequest = ReservationChange::with('reservation', 'requestor')->findOrFail($id);

        if ($changeRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'This change request has already been ' . $changeRequest->status);
        }

        DB::transaction(function () use ($changeRequest, $request) {
            // Update change request status
            $changeRequest->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Create history record
            $changeRequest->reservation->history()->create([
                'performed_by' => Auth::id(),
                'action' => 'changes_rejected',
                'remarks' => 'Admin rejected changes: ' . $request->rejection_reason,
                'performed_at' => now(),
            ]);

            // Notify requestor that changes were rejected
            NotificationHelper::make([
                'user_id' => $changeRequest->requested_by,
                'type' => NotificationHelper::TYPE_EDIT_REJECTED,
                'title' => 'Reservation Changes Rejected',
                'message' => 'Your requested changes to reservation #' . $changeRequest->reservation->reservation_id . ' have been rejected.',
                'reservation_id' => $changeRequest->reservation->reservation_id,
                'data' => [
                    'action' => 'changes_rejected',
                    'rejected_by' => Auth::user()->full_name,
                    'reason' => $request->rejection_reason,
                ],
                'is_read' => false,
            ]);
        });

        return redirect()->route('admin.change-requests.index')
            ->with('status', 'Change request rejected');
    }
}

<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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

        // Only approve if status is pending
        if ($reservation->status !== 'pending') {
            return Redirect::back()
                ->with('error', 'This reservation is no longer pending adviser approval.');
        }

        $remarks = $request->input('remarks', 'Approved by organization adviser');

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
        $this->notificationService->notifyAdviserApproved($reservation, $remarks);

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

        // Only reject if status is pending
        if ($reservation->status !== 'pending') {
            return Redirect::back()
                ->with('error', 'This reservation is no longer pending adviser approval.');
        }

        $reason = $request->input('reason');

        $reservation->update([
            'status' => 'rejected',
            'adviser_responded_at' => now(),
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'adviser_rejected',
            'remarks' => 'Rejected by adviser: ' . $reason,
            'performed_at' => now(),
        ]);

        // Send rejection notifications
        $this->notificationService->notifyAdviserRejected($reservation, $reason);

        return Redirect::back()
            ->with('status', 'reservation-rejected')
            ->with('message', 'Reservation rejected. The requestor has been notified.');
    }
}


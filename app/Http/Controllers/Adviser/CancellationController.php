<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\ReservationCancellation;
use App\Models\ReservationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancellationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
    }

    /**
     * Show cancellation details
     */
    public function show($id)
    {
        $cancellation = ReservationCancellation::with([
            'reservation.organization',
            'reservation.assignedPriest',
            'requestor',
            'staffConfirmer',
            'adminConfirmer',
            'adviserConfirmer',
            'priestConfirmer'
        ])->findOrFail($id);

        // Ensure this adviser is related to the organization
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        if (!$adviserOrgIds->contains($cancellation->reservation->org_id)) {
            abort(403, 'Unauthorized access to this cancellation.');
        }

        return view('adviser.cancellations.show', compact('cancellation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        // Ensure this adviser is related to the organization
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        if (!$adviserOrgIds->contains($cancellation->reservation->org_id)) {
            abort(403, 'Unauthorized access to this cancellation.');
        }

        // Check if already confirmed by this adviser
        if ($cancellation->isAdviserConfirmed()) {
            return redirect()->route('adviser.cancellations.show', $id)
                ->with('info', 'You have already confirmed this cancellation.');
        }

        // Mark as confirmed by adviser
        $cancellation->update([
            'adviser_confirmed_at' => now(),
            'adviser_confirmed_by' => Auth::id(),
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_confirmed_by_adviser',
            'details' => 'Cancellation confirmed by adviser ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        // Check if all required confirmations are done
        if ($cancellation->isFullyConfirmed()) {
            $this->completeCancellation($cancellation);
        }

        return redirect()->route('adviser.cancellations.show', $id)
            ->with('success', 'Cancellation confirmed successfully.');
    }

    /**
     * Complete the cancellation process
     */
    private function completeCancellation(ReservationCancellation $cancellation)
    {
        // Update cancellation status
        $cancellation->update([
            'status' => 'completed',
        ]);

        // Update reservation status
        $cancellation->reservation->update([
            'status' => 'cancelled',
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_completed',
            'details' => 'Reservation cancelled - all parties confirmed',
            'performed_by' => Auth::id(),
        ]);

        // TODO: Send notification to requestor
        // Will be implemented with CancellationNotificationService->notifyCancellationCompleted()
    }
}

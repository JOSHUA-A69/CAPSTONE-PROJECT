<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationCancellation;
use App\Models\ReservationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancellationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
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

        return view('admin.cancellations.show', compact('cancellation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        // Check if already confirmed by this admin
        if ($cancellation->isAdminConfirmed()) {
            return redirect()->route('admin.cancellations.show', $id)
                ->with('info', 'You have already confirmed this cancellation.');
        }

        // Mark as confirmed by admin
        $cancellation->update([
            'admin_confirmed_at' => now(),
            'admin_confirmed_by' => Auth::id(),
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_confirmed_by_admin',
            'details' => 'Cancellation confirmed by admin ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        // Check if all required confirmations are done
        if ($cancellation->isFullyConfirmed()) {
            $this->completeCancellation($cancellation);
        }

        return redirect()->route('admin.cancellations.show', $id)
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

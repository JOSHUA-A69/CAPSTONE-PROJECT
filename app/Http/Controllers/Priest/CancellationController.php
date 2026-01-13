<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\ReservationCancellation;
use App\Models\ReservationHistory;
use App\Services\CancellationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancellationController extends Controller
{
    protected CancellationNotificationService $cancellationService;

    public function __construct(CancellationNotificationService $cancellationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':priest']);
        $this->cancellationService = $cancellationService;
    }

    /**
     * List cancellation requests
     */
    public function index()
    {
        $priestId = Auth::id();

        $cancellations = ReservationCancellation::whereHas('reservation', function($q) use ($priestId) {
                // Check if priest is primary officiant OR in the list of assigned priests
                $q->where('officiant_id', $priestId)
                  ->orWhereHas('priests', function($pq) use ($priestId) {
                      $pq->where('users.id', $priestId);
                  });
            })
            ->with(['reservation.organization', 'requestor'])
            ->orderByRaw('priest_confirmed_at IS NULL DESC') // Pending first
            ->latest()
            ->paginate(10);

        return view('priest.cancellations.index', compact('cancellations'));
    }

    /**
     * Show cancellation details
     */
    public function show($id)
    {
        $cancellation = ReservationCancellation::with([
            'reservation.organization',
            'reservation.officiant',
            'requestor',
            'staffConfirmer',
            'adminConfirmer',
            'adviserConfirmer',
            'priestConfirmer'
        ])->findOrFail($id);

        // Ensure this priest is the assigned priest
        $reservation = $cancellation->reservation;
        $isAssigned = $reservation->officiant_id === Auth::id() || 
                      $reservation->priests()->where('users.id', Auth::id())->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized access to this cancellation.');
        }

        return view('priest.cancellations.show', compact('cancellation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        // Ensure this priest is the assigned priest
        $reservation = $cancellation->reservation;
        $isAssigned = $reservation->officiant_id === Auth::id() || 
                      $reservation->priests()->where('users.id', Auth::id())->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized access to this cancellation.');
        }

        // Check if already confirmed by this priest
        if ($cancellation->isPriestConfirmed()) {
            return redirect()->route('priest.cancellations.show', $id)
                ->with('info', 'You have already confirmed this cancellation.');
        }

        // Mark as confirmed by priest
        $cancellation->update([
            'priest_confirmed_at' => now(),
            'priest_confirmed_by' => Auth::id(),
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_confirmed_by_priest',
            'details' => 'Cancellation confirmed by priest ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        // Check if all required confirmations are done
        if ($cancellation->isFullyConfirmed()) {
            $this->completeCancellation($cancellation);
        }

        return redirect()->route('priest.cancellations.show', $id)
            ->with('success', 'Cancellation confirmed successfully.');
    }

    /**
     * Reject cancellation
     */
    public function reject($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        // Ensure this priest is the assigned priest
        $reservation = $cancellation->reservation;
        $isAssigned = $reservation->officiant_id === Auth::id() || 
                      $reservation->priests()->where('users.id', Auth::id())->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized access to this cancellation.');
        }

        // Check if already processed
        if ($cancellation->status === 'rejected') {
            return redirect()->route('priest.cancellations.show', $id)
                ->with('info', 'This cancellation has already been rejected.');
        }

        // Mark as rejected
        $cancellation->update([
            'status' => 'rejected',
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_rejected_by_priest',
            'details' => 'Cancellation request rejected by priest ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        // Send notification
        $this->cancellationService->notifyCancellationRejected(
            $cancellation, 
            Auth::user()->name, // Or formatted displayName if available, but Auth::user()->name is standard
            'Priest'
        );

        return redirect()->route('priest.cancellations.show', $id)
            ->with('success', 'Cancellation request rejected. The reservation remains active.');
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

        // Send completion notifications
        $this->cancellationService->notifyCancellationCompleted($cancellation->reservation, $cancellation);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationCancellation;
use App\Models\ReservationHistory;
use App\Services\CancellationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CancellationController extends Controller
{
    protected CancellationNotificationService $cancellationService;

    public function __construct(CancellationNotificationService $cancellationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
        $this->cancellationService = $cancellationService;
    }

    /**
     * List pending/in-progress cancellations
     */
    public function index(Request $request)
    {
        $query = ReservationCancellation::with([
            'reservation.organization',
            'reservation.officiant',
            'requestor',
            'staffConfirmer',
            'adminConfirmer',
            'adviserConfirmer',
            'priestConfirmer'
        ])->orderByDesc('created_at');

        // Show only pending/in-progress by default
        $status = $request->get('status', 'pending');
        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        }

        $cancellations = $query->paginate(20);

        return view('admin.cancellations.index', compact('cancellations', 'status'));
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
     * Reject cancellation
     */
    public function reject($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        if ($cancellation->status === 'rejected') {
            return redirect()->route('admin.cancellations.show', $id)
                ->with('info', 'This cancellation has already been rejected.');
        }

        $cancellation->update([
            'status' => 'rejected',
        ]);

        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_rejected_by_admin',
            'details' => 'Cancellation request rejected by admin ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        $this->cancellationService->notifyCancellationRejected(
            $cancellation, 
            Auth::user()->name, 
            'Admin'
        );

        return redirect()->route('admin.cancellations.show', $id)
            ->with('success', 'Cancellation request rejected. The reservation remains active.');
    }

    /**
     * Complete the cancellation process
     */
    private function completeCancellation(ReservationCancellation $cancellation)
    {
        $reservation = $cancellation->reservation;
        
        // Update cancellation status
        $cancellation->update([
            'status' => 'completed',
        ]);

        // Update reservation status
        $reservation->update([
            'status' => 'cancelled',
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $reservation->reservation_id,
            'action' => 'cancellation_completed',
            'details' => 'Reservation cancelled - all parties confirmed',
            'performed_by' => Auth::id(),
        ]);
        // Send completion notifications via centralized service
        $this->cancellationService->notifyCancellationCompleted($cancellation->reservation, $cancellation);
    }
}

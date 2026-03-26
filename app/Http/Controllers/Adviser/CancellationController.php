<?php

namespace App\Http\Controllers\Adviser;

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
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
        $this->cancellationService = $cancellationService;
    }

    /**
     * List cancellation requests
     */
    public function index()
    {
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');

        $cancellations = ReservationCancellation::whereHas('reservation', function($q) use ($adviserOrgIds) {
                $q->whereIn('org_id', $adviserOrgIds)
                  ->orWhereHas('organizations', function($sq) use ($adviserOrgIds) {
                      $sq->whereIn('organizations.org_id', $adviserOrgIds);
                  });
            })
            ->with(['reservation.organization', 'requestor'])
            ->orderByRaw('adviser_confirmed_at IS NULL DESC') // Pending first
            ->latest()
            ->paginate(10);

        return view('adviser.cancellations.index', compact('cancellations'));
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

        // Ensure this adviser is related to the organization (either main org or shared)
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        $hasAccess = $adviserOrgIds->contains($cancellation->reservation->org_id) ||
                     $cancellation->reservation->organizations()->whereIn('organizations.org_id', $adviserOrgIds)->exists();

        if (!$hasAccess) {
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

        // Ensure this adviser is related to the organization (either main org or shared)
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        $hasAccess = $adviserOrgIds->contains($cancellation->reservation->org_id) ||
                     $cancellation->reservation->organizations()->whereIn('organizations.org_id', $adviserOrgIds)->exists();

        if (!$hasAccess) {
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
     * Reject cancellation
     */
    public function reject($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        // Ensure this adviser is related to the organization (either main org or shared)
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        $hasAccess = $adviserOrgIds->contains($cancellation->reservation->org_id) ||
                     $cancellation->reservation->organizations()->whereIn('organizations.org_id', $adviserOrgIds)->exists();

        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this cancellation.');
        }

        // Check if already processed
        if ($cancellation->status === 'rejected') {
            return redirect()->route('adviser.cancellations.show', $id)
                ->with('info', 'This cancellation has already been rejected.');
        }

        // Mark as rejected
        $cancellation->update([
            'status' => 'rejected',
            // We do NOT set adviser_confirmed_at because they rejected it, not confirmed it.
            // But if we want to indicate they "handled" it, we might want to track that separately.
            // For now, status='rejected' is enough to stop the process.
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_rejected_by_adviser',
            'details' => 'Cancellation request rejected by adviser ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        // Send notification
        $this->cancellationService->notifyCancellationRejected(
            $cancellation, 
            Auth::user()->name, 
            'Adviser'
        );

        return redirect()->route('adviser.cancellations.show', $id)
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

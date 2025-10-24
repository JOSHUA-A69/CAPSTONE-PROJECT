<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
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
        // Treat {id} as reservation_id now
        $reservation = Reservation::with(['organization', 'officiant', 'user', 'service', 'venue'])->findOrFail($id);

        // Ensure this adviser is related to the organization
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        if (!$adviserOrgIds->contains($reservation->org_id)) {
            abort(403, 'Unauthorized access to this reservation.');
        }

        return view('adviser.cancellations.show', compact('reservation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Ensure this adviser is related to the organization
        $adviserOrgIds = Auth::user()->organizations->pluck('org_id');
        if (!$adviserOrgIds->contains($reservation->org_id)) {
            abort(403, 'Unauthorized access to this reservation.');
        }

        if ($reservation->status === 'cancelled') {
            return redirect()->route('adviser.cancellations.show', $id)
                ->with('info', 'This reservation is already cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        ReservationHistory::create([
            'reservation_id' => $reservation->reservation_id,
            'action' => 'cancelled',
            'remarks' => 'Cancelled by adviser confirmation',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return redirect()->route('adviser.cancellations.show', $id)
            ->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Complete the cancellation process
     */
    private function completeCancellation($noop) { /* not used */ }
}

<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancellationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':priest']);
    }

    /**
     * Show cancellation details
     */
    public function show($id)
    {
        $reservation = Reservation::with(['organization', 'officiant', 'user', 'service', 'venue'])->findOrFail($id);

        // Ensure this priest is the assigned priest
        if ($reservation->officiant_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this reservation.');
        }

        return view('priest.cancellations.show', compact('reservation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Ensure this priest is the assigned priest
        if ($reservation->officiant_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this reservation.');
        }

        if ($reservation->status === 'cancelled') {
            return redirect()->route('priest.cancellations.show', $id)
                ->with('info', 'This reservation is already cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        ReservationHistory::create([
            'reservation_id' => $reservation->reservation_id,
            'action' => 'cancelled',
            'remarks' => 'Cancelled by priest confirmation',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return redirect()->route('priest.cancellations.show', $id)
            ->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Complete the cancellation process
     */
    private function completeCancellation($noop) { /* not used */ }
}

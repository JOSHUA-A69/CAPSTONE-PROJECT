<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
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
        // Treat {id} as reservation_id going forward (no separate cancellation table)
        $reservation = Reservation::with(['organization', 'officiant', 'user', 'service', 'venue'])
            ->findOrFail($id);

        return view('admin.cancellations.show', compact('reservation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status === 'cancelled') {
            return redirect()->route('admin.cancellations.show', $id)
                ->with('info', 'This reservation is already cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        ReservationHistory::create([
            'reservation_id' => $reservation->reservation_id,
            'action' => 'cancelled',
            'remarks' => 'Cancelled by admin confirmation',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return redirect()->route('admin.cancellations.show', $id)
            ->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Complete the cancellation process
     */
    // No separate completion step needed with simplified flow
    private function completeCancellation($noop) { /* intentionally empty */ }
}

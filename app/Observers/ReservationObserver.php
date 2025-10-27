<?php

namespace App\Observers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // Safety net: when a reservation is created, ensure a 'submitted' history entry exists
        try {
            $exists = $reservation->history()
                ->where('action', 'submitted')
                ->exists();

            if (!$exists) {
                $reservation->history()->create([
                    'performed_by' => Auth::id(),
                    'action' => 'submitted',
                    'remarks' => 'Reservation submitted',
                    'performed_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Do not block the flow; logging is optional to avoid noise
        }
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Safety net 1: contacted_at stamped
        try {
            if ($reservation->wasChanged('contacted_at') && $reservation->contacted_at && empty($reservation->getOriginal('contacted_at'))) {
                $recent = $reservation->history()
                    ->where('action', 'contacted_requestor')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->exists();

                if (!$recent) {
                    $reservation->history()->create([
                        'performed_by' => Auth::id(),
                        'action' => 'contacted_requestor',
                        'remarks' => 'Staff contacted requestor (observer fallback)',
                        'performed_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // swallow
        }

        // Safety net 2: status changes
        try {
            if ($reservation->wasChanged('status')) {
                $old = $reservation->getOriginal('status');
                $new = $reservation->status;

                $recent = $reservation->history()
                    ->where('action', 'status_updated')
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->exists();

                if (!$recent) {
                    $reservation->history()->create([
                        'performed_by' => Auth::id(),
                        'action' => 'status_updated',
                        'remarks' => sprintf('Status changed: %s → %s', $old ?? 'none', $new ?? 'none'),
                        'performed_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // swallow
        }
    }
}

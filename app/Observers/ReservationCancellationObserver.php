<?php

namespace App\Observers;

use App\Models\ReservationCancellation;
use App\Models\User;
use App\Services\SSEService;

class ReservationCancellationObserver
{
    /**
     * Handle the ReservationCancellation "created" event.
     */
    public function created(ReservationCancellation $cancellation): void
    {
        $this->notifyRelevantUsers($cancellation, 'created');
    }

    /**
     * Handle the ReservationCancellation "updated" event.
     */
    public function updated(ReservationCancellation $cancellation): void
    {
        $this->notifyRelevantUsers($cancellation, 'updated');
    }

    /**
     * Notify all relevant users about cancellation changes
     */
    protected function notifyRelevantUsers(ReservationCancellation $cancellation, string $action): void
    {
        $userIds = [];

        // Load the reservation if not loaded
        $reservation = $cancellation->reservation;

        // Notify admin and staff
        $adminStaffIds = User::whereIn('role', ['admin', 'staff'])
            ->pluck('id')
            ->toArray();
        $userIds = array_merge($userIds, $adminStaffIds);

        // Notify the requestor
        if ($reservation && $reservation->user_id) {
            $userIds[] = $reservation->user_id;
        }

        // Notify the assigned priest if any
        if ($reservation && $reservation->officiant_id) {
            $userIds[] = $reservation->officiant_id;
        }

        // Notify the adviser if applicable
        if ($cancellation->adviser_id) {
            $userIds[] = $cancellation->adviser_id;
        }

        // Remove duplicates
        $userIds = array_unique($userIds);

        // Trigger SSE update
        SSEService::triggerUpdate('cancellations', $userIds, [
            'action' => $action,
            'cancellation_id' => $cancellation->id,
            'status' => $cancellation->status,
        ]);
    }
}

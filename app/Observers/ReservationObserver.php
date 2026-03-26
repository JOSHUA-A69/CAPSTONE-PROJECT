<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\User;
use App\Services\SSEService;
use App\Services\RealtimeUpdateService;
use Illuminate\Support\Facades\DB;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        $this->notifyRelevantUsers($reservation, 'created');
        $this->broadcastRealtimeUpdate($reservation, 'create');
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        $this->notifyRelevantUsers($reservation, 'updated');
        $this->broadcastRealtimeUpdate($reservation, 'update');
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        $this->notifyRelevantUsers($reservation, 'deleted');
        RealtimeUpdateService::broadcast('delete', 'Reservation', [
            'id' => $reservation->id,
        ], [$reservation->user_id, 'admin', 'staff']);
    }

    /**
     * Notify all relevant users about reservation changes
     */
    protected function notifyRelevantUsers(Reservation $reservation, string $action): void
    {
        $userIds = [];

        // Always notify admin and staff
        $adminStaffIds = User::whereIn('role', ['admin', 'staff'])
            ->pluck('id')
            ->toArray();
        $userIds = array_merge($userIds, $adminStaffIds);

        // Notify the requestor
        if ($reservation->user_id) {
            $userIds[] = $reservation->user_id;
        }

        // Notify the assigned priest
        if ($reservation->officiant_id) {
            $userIds[] = $reservation->officiant_id;
        }

        // Notify the adviser(s) for the organization
        if ($reservation->org_id) {
            $adviserIds = DB::table('organizations')
                ->where('org_id', $reservation->org_id)
                ->whereNotNull('adviser_id')
                ->pluck('adviser_id')
                ->toArray();
            $userIds = array_merge($userIds, $adviserIds);
        }

        // Remove duplicates
        $userIds = array_unique($userIds);

        // Trigger SSE update
        SSEService::triggerUpdate('reservations', $userIds, [
            'action' => $action,
            'reservation_id' => $reservation->reservation_id,
            'status' => $reservation->status,
        ]);
    }

    /**
     * Broadcast real-time update for this reservation
     */
    protected function broadcastRealtimeUpdate(Reservation $reservation, string $type): void
    {
        $reservation->load(['user', 'service', 'organization']);

        // Get list of affected user IDs
        $userIds = [];

        // Notify the requestor
        if ($reservation->user_id) {
            $userIds[] = $reservation->user_id;
        }

        // Notify the assigned priest
        if ($reservation->officiant_id) {
            $userIds[] = $reservation->officiant_id;
        }

        // Notify adviser if organization booking
        if ($reservation->organization_id) {
            RealtimeUpdateService::broadcastReservationUpdate($type, $reservation, ['staff', 'admin']);

            $adviserIds = $reservation->organization?->adviser_id
                ? [$reservation->organization->adviser_id]
                : [];
            if (!empty($adviserIds)) {
                RealtimeUpdateService::broadcastReservationUpdate($type, $reservation, $adviserIds);
            }
        } else {
            // General broadcast to admin and staff for non-org reservations
            RealtimeUpdateService::broadcastReservationUpdate($type, $reservation, ['admin', 'staff']);
        }

        // Broadcast to the affected users
        if (!empty($userIds)) {
            RealtimeUpdateService::broadcastReservationUpdate($type, $reservation, $userIds);
        }
    }
}

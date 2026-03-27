<?php

namespace App\Observers;

use App\Models\OrganizationBookingRequest;
use App\Models\User;
use App\Services\SSEService;
use App\Services\RealtimeUpdateService;
use Illuminate\Support\Facades\DB;

class OrganizationBookingRequestObserver
{
    /**
     * Handle the OrganizationBookingRequest "created" event.
     */
    public function created(OrganizationBookingRequest $booking): void
    {
        $this->notifyRelevantUsers($booking, 'created');
        $this->broadcastRealtimeUpdate($booking, 'create');
    }

    /**
     * Handle the OrganizationBookingRequest "updated" event.
     */
    public function updated(OrganizationBookingRequest $booking): void
    {
        $this->notifyRelevantUsers($booking, 'updated');
        $this->broadcastRealtimeUpdate($booking, 'update');
    }

    /**
     * Handle the OrganizationBookingRequest "deleted" event.
     */
    public function deleted(OrganizationBookingRequest $booking): void
    {
        $this->notifyRelevantUsers($booking, 'deleted');
        RealtimeUpdateService::broadcast('delete', 'OrganizationBooking', [
            'id' => $booking->id,
        ], [$booking->user_id, 'admin', 'staff']);
    }

    /**
     * Notify all relevant users about organization booking changes
     */
    protected function notifyRelevantUsers(OrganizationBookingRequest $booking, string $action): void
    {
        $userIds = [];

        // Notify admin and staff
        $adminStaffIds = User::whereIn('role', ['admin', 'staff'])
            ->pluck('id')
            ->toArray();
        $userIds = array_merge($userIds, $adminStaffIds);

        // Notify the requestor
        if ($booking->user_id) {
            $userIds[] = $booking->user_id;
        }

        // Notify the adviser for the organization
        if ($booking->org_id) {
            $adviserId = DB::table('organizations')
                ->where('org_id', $booking->org_id)
                ->value('adviser_id');

            if ($adviserId) {
                $userIds[] = $adviserId;
            }
        }

        // Remove duplicates
        $userIds = array_unique($userIds);

        // Trigger SSE update
        SSEService::triggerUpdate('org_bookings', $userIds, [
            'action' => $action,
            'booking_id' => $booking->id ?? $booking->booking_id ?? null,
            'status' => $booking->status,
        ]);
    }

    /**
     * Broadcast real-time update for this organization booking
     */
    protected function broadcastRealtimeUpdate(OrganizationBookingRequest $booking, string $type): void
    {
        $booking->load(['organization']);

        // Broadcast to user who made the request
        if ($booking->user_id) {
            RealtimeUpdateService::broadcastOrgBookingUpdate($type, $booking, [$booking->user_id]);
        }

        // Broadcast to admin and staff
        RealtimeUpdateService::broadcastOrgBookingUpdate($type, $booking, ['admin', 'staff']);

        // Broadcast to adviser if organization has one
        if ($booking->org_id) {
            $adviser_id = $booking->organization?->adviser_id;
            if ($adviser_id) {
                RealtimeUpdateService::broadcastOrgBookingUpdate($type, $booking, [$adviser_id]);
            }
        }
    }
}

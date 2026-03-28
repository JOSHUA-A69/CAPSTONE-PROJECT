<?php

namespace App\Observers;

use App\Models\User;
use App\Services\RealtimeUpdateService;
use App\Services\SSEService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Notify admins about new user registration
        SSEService::triggerRoleUpdate('users', 'admin', [
            'action' => 'user_created',
            'user_id' => $user->id,
            'role' => $user->role,
            'status' => $user->status,
        ]);

        RealtimeUpdateService::broadcast('create', 'User', $this->buildRealtimePayload($user), [
            'admin',
            'staff',
            'adviser',
            'priest',
            'requestor',
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Notify admins about user updates
        SSEService::triggerRoleUpdate('users', 'admin', [
            'action' => 'user_updated',
            'user_id' => $user->id,
            'role' => $user->role,
            'status' => $user->status,
        ]);

        RealtimeUpdateService::broadcast('update', 'User', $this->buildRealtimePayload($user), [
            'admin',
            'staff',
            'adviser',
            'priest',
            'requestor',
            $user->id,
        ]);

        // If user status changed, notify the user themselves
        if ($user->isDirty('status')) {
            SSEService::triggerUpdate('users', [$user->id], [
                'action' => 'status_changed',
                'new_status' => $user->status,
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Notify admins about user deletion/archiving
        SSEService::triggerRoleUpdate('users', 'admin', [
            'action' => 'user_deleted',
            'user_id' => $user->id,
        ]);

        RealtimeUpdateService::broadcast('delete', 'User', [
            'id' => $user->id,
            'status' => $user->status,
            'deleted_at' => optional($user->deleted_at)?->toDateTimeString(),
        ], [
            'admin',
            'staff',
            'adviser',
            'priest',
            'requestor',
            $user->id,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Notify admins about user restoration
        SSEService::triggerRoleUpdate('users', 'admin', [
            'action' => 'user_restored',
            'user_id' => $user->id,
        ]);

        RealtimeUpdateService::broadcast('restore', 'User', $this->buildRealtimePayload($user), [
            'admin',
            'staff',
            'adviser',
            'priest',
            'requestor',
            $user->id,
        ]);
    }

    /**
     * Build consistent payload used by SSE CRUD listeners.
     */
    protected function buildRealtimePayload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'deleted_at' => optional($user->deleted_at)?->toDateTimeString(),
        ];
    }
}

<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountActivated;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HandleEmailVerified
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        // Ensure the event user is our Eloquent User model before calling Eloquent methods
        if (! $user instanceof User) {
            return;
        }

        // On email verification, mark the user active regardless of role.
        // This lets registrations for any role be created but requires the
        // user to verify their email before being considered active.
        $user->status = 'active';
        $user->save();

        // Send activation notification to the user.
        try {
            Mail::to($user->email)->send(new AccountActivated($user));
        } catch (\Throwable $e) {
            // Don't break the verification flow if mail fails; log for diagnostics.
            Log::error('Failed to send AccountActivated mail: '.$e->getMessage());
        }
    }
}

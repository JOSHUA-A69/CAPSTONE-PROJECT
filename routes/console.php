<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use App\Models\User;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Schedule the check for unnoticed reservations command
 * Runs daily at 9:00 AM to send follow-up notifications to advisers
 */
Schedule::command('reservations:check-unnoticed --send-notifications')
    ->dailyAt('09:00')
    ->emailOutputOnFailure(config('mail.from.address'))
    ->description('Check for unnoticed reservation requests and send follow-ups');

/**
 * Schedule the organization booking overdue check command
 * Runs daily at 10:00 AM to send reminders for overdue organization booking requests
 */
Schedule::command('organization-bookings:process-overdue')
    ->dailyAt('10:00')
    ->emailOutputOnFailure(config('mail.from.address'))
    ->description('Process overdue organization booking requests and send staff reminders');

/**
 * Chat utilities: unread count and mark-all-unread.
 * These are CLI-only helpers useful for diagnosing the navbar badge.
 */
Artisan::command('chat:unread-count {--email=} {--id=}', function () {
    /** @var ClosureCommand $this */
    $email = (string) $this->option('email');
    $id = $this->option('id');

    $user = null;
    if (!empty($id)) {
        $user = User::find($id);
    } elseif (!empty($email)) {
        $user = User::where('email', $email)->first();
    }

    if (!$user) {
        $user = User::where('role', 'admin')->orderBy('id')->first();
        if ($user) {
            $this->warn("No --email/--id provided. Defaulting to first admin: {$user->email} (ID {$user->id}).");
        } else {
            $this->error('Provide a valid --email or --id for the user. No admin user found as fallback.');
            return 1;
        }
    }

    $count = DB::table('messages')
        ->leftJoin('chat_resets as cr', function ($join) use ($user) {
            $join->on('cr.other_user_id', '=', 'messages.sender_id')
                 ->where('cr.user_id', '=', $user->id);
        })
        ->where('messages.receiver_id', $user->id)
        ->where('messages.sender_id', '<>', $user->id)
        ->whereNull('messages.read_at')
        ->where(function ($q) {
            $q->whereNull('cr.cleared_at')
              ->orWhereColumn('messages.created_at', '>', 'cr.cleared_at');
        })
        ->distinct()
        ->count('messages.id');

    $this->info("Unread count for {$user->email} (ID {$user->id}): {$count}");
    return 0;
})->purpose('Show unread chat count for a specific user');

Artisan::command('chat:mark-all-unread {--email=} {--id=} {--dry-run}', function () {
    /** @var ClosureCommand $this */
    $email = (string) $this->option('email');
    $id = $this->option('id');
    $dryRun = (bool) $this->option('dry-run');

    $user = null;
    if (!empty($id)) {
        $user = User::find($id);
    } elseif (!empty($email)) {
        $user = User::where('email', $email)->first();
    }

    if (!$user) {
        $user = User::where('role', 'admin')->orderBy('id')->first();
        if ($user) {
            $this->warn("No --email/--id provided. Defaulting to first admin: {$user->email} (ID {$user->id}).");
        } else {
            $this->error('Provide a valid --email or --id for the user. No admin user found as fallback.');
            return 1;
        }
    }

    $query = DB::table('messages')
        ->where('receiver_id', $user->id)
        ->where('sender_id', '<>', $user->id)
        ->whereNull('read_at');

    $toUpdate = (clone $query)->count();
    if ($toUpdate === 0) {
        $this->info("No unread messages for {$user->email} (ID {$user->id}).");
        return 0;
    }

    if ($dryRun) {
        $this->warn("[Dry-run] Would mark {$toUpdate} messages as read for {$user->email} (ID {$user->id}).");
        return 0;
    }

    $affected = $query->update(['read_at' => now()]);
    $this->info("Marked {$affected} messages as read for {$user->email} (ID {$user->id}).");
    return 0;
})->purpose('Mark all unread chat messages as read for a specific user');

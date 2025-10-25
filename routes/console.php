<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

// Daily digest of recent cancellations to ensure staff awareness
Schedule::command('reservations:check-cancellations --since=24')
    ->dailyAt('10:00')
    ->emailOutputOnFailure(config('mail.from.address'))
    ->description('Send daily digest for recent cancellations to Admin/Staff');

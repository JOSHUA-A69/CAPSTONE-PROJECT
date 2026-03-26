<?php

namespace App\Console\Commands;

use App\Models\OrganizationBookingRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireOrganizationBookingSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire organization booking sessions that have timed out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = 0;

        // Get all sessions that have expired
        $expiredBookings = OrganizationBookingRequest::expiredSessions()->get();

        foreach ($expiredBookings as $booking) {
            $booking->expireSession();
            $expiredCount++;

            Log::info('Organization booking session expired', [
                'booking_id' => $booking->id,
                'activity_name' => $booking->activity_name,
                'requestor_id' => $booking->requestor_id,
                'session_started_at' => $booking->session_started_at,
                'session_expires_at' => $booking->session_expires_at
            ]);
        }

        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} organization booking session(s).");
        } else {
            $this->info('No expired sessions found.');
        }

        return Command::SUCCESS;
    }
}

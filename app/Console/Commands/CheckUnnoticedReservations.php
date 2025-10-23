<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\ReservationNotificationService;
use Illuminate\Console\Command;

/**
 * Check for Unnoticed Reservation Requests
 *
 * This command runs daily (via task scheduler) to detect reservation requests
 * that have been pending for more than 24 hours without adviser response.
 *
 * It automatically sends follow-up notifications to advisers and alerts CREaM staff.
 */
class CheckUnnoticedReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:check-unnoticed
                          {--send-notifications : Actually send notifications (default is dry-run)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for reservation requests pending adviser approval for >24 hours and send follow-up notifications';

    protected ReservationNotificationService $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(ReservationNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking for unnoticed reservation requests...');
        $this->newLine();

        // Get all unnoticed requests
        $unnoticedReservations = Reservation::with(['user', 'service', 'organization.adviser'])
            ->unnoticedByAdviser()
            ->get();

        if ($unnoticedReservations->isEmpty()) {
            $this->info('✅ No unnoticed requests found. All reservations are being processed.');
            return self::SUCCESS;
        }

        $this->warn("⚠️  Found {$unnoticedReservations->count()} unnoticed request(s):");
        $this->newLine();

        $table = [];
        foreach ($unnoticedReservations as $reservation) {
            $table[] = [
                'ID' => $reservation->reservation_id,
                'Service' => $reservation->service->service_name,
                'Requestor' => $reservation->user->full_name,
                'Organization' => $reservation->organization->org_name ?? 'N/A',
                'Adviser' => $reservation->organization->adviser->full_name ?? 'N/A',
                'Submitted' => $reservation->created_at->diffForHumans(),
                'Last Follow-up' => $reservation->staff_followed_up_at?->diffForHumans() ?? 'Never',
            ];
        }

        $this->table(
            ['ID', 'Service', 'Requestor', 'Organization', 'Adviser', 'Submitted', 'Last Follow-up'],
            $table
        );

        $this->newLine();

        // Check if we should send notifications
        if (!$this->option('send-notifications')) {
            $this->comment('ℹ️  Dry run mode. Use --send-notifications flag to actually send follow-ups.');
            return self::SUCCESS;
        }

        // Send follow-up notifications
        $this->info('📧 Sending follow-up notifications...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($unnoticedReservations->count());
        $progressBar->start();

        $sentCount = 0;
        $errorCount = 0;

        foreach ($unnoticedReservations as $reservation) {
            try {
                // Update staff follow-up timestamp
                $reservation->update([
                    'staff_followed_up_at' => now(),
                ]);

                // Create history
                $reservation->history()->create([
                    'performed_by' => null, // System-generated
                    'action' => 'staff_followed_up',
                    'remarks' => 'Automated follow-up sent to adviser - request pending >24 hours',
                    'performed_at' => now(),
                ]);

                // Send notification
                $this->notificationService->notifyAdviserFollowUp($reservation);

                $sentCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send notification for Reservation #{$reservation->reservation_id}: " . $e->getMessage());
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Follow-up notifications sent: {$sentCount}");
        if ($errorCount > 0) {
            $this->error("❌ Failed notifications: {$errorCount}");
        }

        $this->newLine();
        $this->info('✨ Process complete!');

        return self::SUCCESS;
    }
}

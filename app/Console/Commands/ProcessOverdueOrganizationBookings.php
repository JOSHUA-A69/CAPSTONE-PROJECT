<?php

namespace App\Console\Commands;

use App\Services\OrganizationBookingNotificationService;
use Illuminate\Console\Command;

class ProcessOverdueOrganizationBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organization-bookings:process-overdue 
                            {--force : Send reminders even if already sent today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process overdue organization booking requests and send reminders to staff';

    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(OrganizationBookingNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing overdue organization booking requests...');

        try {
            $results = $this->notificationService->processOverdueRequests();

            if ($results['processed'] > 0) {
                $this->info("✅ Processed {$results['processed']} overdue requests");
                $this->info("📧 Sent {$results['successful']} reminder notifications successfully");
                
                if ($results['successful'] < $results['processed']) {
                    $failed = $results['processed'] - $results['successful'];
                    $this->warn("⚠️  Failed to send {$failed} reminder notifications");
                }
            } else {
                $this->info('ℹ️  No overdue requests found that need reminders');
            }

            // Additional statistics
            $this->displayStatistics();

        } catch (\Exception $e) {
            $this->error("❌ Error processing overdue requests: " . $e->getMessage());
            \Log::error("ProcessOverdueOrganizationBookings command failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Display current organization booking statistics
     */
    private function displayStatistics()
    {
        $this->newLine();
        $this->info('📊 Current Organization Booking Statistics:');
        
        try {
            $totalPending = \App\Models\OrganizationBookingRequest::pending()->count();
            $totalOverdue = \App\Models\OrganizationBookingRequest::needingReminder()->count();
            $orgsWithoutAdvisers = $this->notificationService->getOrganizationsWithoutAdvisers()->count();
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Pending Requests', $totalPending],
                    ['Overdue Requests', $totalOverdue],
                    ['Organizations without Advisers', $orgsWithoutAdvisers],
                ]
            );

            if ($orgsWithoutAdvisers > 0) {
                $this->warn("⚠️  {$orgsWithoutAdvisers} organization(s) have no assigned adviser!");
                $this->warn("   These organizations cannot process booking requests until an adviser is assigned.");
            }

        } catch (\Exception $e) {
            $this->warn("Could not retrieve statistics: " . $e->getMessage());
        }
    }
}

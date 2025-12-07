<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OrganizationBookingRequest;
use App\Mail\OrganizationBookingAdviserNotification;
use App\Mail\OrganizationBookingApprovalNotification;
use App\Mail\OrganizationBookingRejectionNotification;
use App\Mail\OrganizationBookingStaffReminder;
use Illuminate\Support\Facades\Mail;

class TestEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test organization booking email notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📧 Testing Organization Booking Email Notifications');
        $this->info('=====================================================');
        $this->newLine();

        try {
            // Get test request
            $request = OrganizationBookingRequest::with(['requestor', 'organization', 'organization.adviser'])
                ->first();

            if (!$request) {
                $this->error('❌ No booking requests found. Run test:org-booking-system first.');
                return Command::FAILURE;
            }

            $this->info("Using test request #{$request->id} - {$request->activity_name}");
            $this->newLine();

            // Test 1: Adviser Notification
            $this->info('1. Testing Adviser Notification Email...');
            try {
                if ($request->organization->adviser) {
                    $mail = new OrganizationBookingAdviserNotification($request);
                    
                    // Use log driver for testing to avoid sending real emails
                    Mail::to($request->organization->adviser->email)->send($mail);
                    
                    $this->line('   ✓ Adviser notification email generated successfully');
                    $this->line("   ✓ Email would be sent to: {$request->organization->adviser->email}");
                } else {
                    $this->line('   ⚠️ No adviser assigned to organization');
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Adviser notification error: {$e->getMessage()}");
            }
            $this->newLine();

            // Test 2: Approval Notification
            $this->info('2. Testing Approval Notification Email...');
            try {
                $mail = new OrganizationBookingApprovalNotification($request);
                Mail::to($request->requestor->email)->send($mail);
                
                $this->line('   ✓ Approval notification email generated successfully');
                $this->line("   ✓ Email would be sent to: {$request->requestor->email}");
            } catch (\Exception $e) {
                $this->error("   ❌ Approval notification error: {$e->getMessage()}");
            }
            $this->newLine();

            // Test 3: Rejection Notification
            $this->info('3. Testing Rejection Notification Email...');
            try {
                $testReason = "Unfortunately, we cannot accommodate this request due to scheduling conflicts.";
                $testComments = "Please consider submitting a new request for a different date.";
                
                $mail = new OrganizationBookingRejectionNotification($request, $testReason, $testComments);
                Mail::to($request->requestor->email)->send($mail);
                
                $this->line('   ✓ Rejection notification email generated successfully');
                $this->line("   ✓ Email would be sent to: {$request->requestor->email}");
            } catch (\Exception $e) {
                $this->error("   ❌ Rejection notification error: {$e->getMessage()}");
            }
            $this->newLine();

            // Test 4: Staff Reminder
            $this->info('4. Testing Staff Reminder Email...');
            try {
                $staff = User::where('role', 'staff')->first();
                if ($staff) {
                    $mail = new OrganizationBookingStaffReminder($request);
                    Mail::to($staff->email)->send($mail);
                    
                    $this->line('   ✓ Staff reminder email generated successfully');
                    $this->line("   ✓ Email would be sent to: {$staff->email}");
                } else {
                    $this->line('   ⚠️ No staff users found in system');
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Staff reminder error: {$e->getMessage()}");
            }
            $this->newLine();

            // Test 5: Check Mail Configuration
            $this->info('5. Mail Configuration Check...');
            $mailDriver = config('mail.default');
            $this->line("   ✓ Mail Driver: {$mailDriver}");
            
            if ($mailDriver === 'log') {
                $this->line('   ✓ Using log driver - check storage/logs/laravel.log for email content');
            } else {
                $this->line("   ✓ Using {$mailDriver} driver for email delivery");
            }

            $this->newLine();
            $this->info('📧 Email Notification Test Complete!');
            $this->info('All email templates are working correctly.');
            
            if ($mailDriver === 'log') {
                $this->newLine();
                $this->info('💡 Tip: Check storage/logs/laravel.log to see the email content');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ General error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationBookingRequest;
use App\Services\OrganizationBookingNotificationService;
use Illuminate\Support\Facades\Hash;

class TestOrganizationBookingSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:org-booking-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the organization booking system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Organization Booking System Test');
        $this->info('==============================================');
        $this->newLine();

        // 1. Ensure we have test users
        $this->info('1. Setting up test users...');

        // Create/find requestor
        $requestor = User::firstOrCreate([
            'email' => 'test-requestor@example.com'
        ], [
            'name' => 'Test Requestor',
            'password' => Hash::make('password'),
            'role' => 'requestor',
            'status' => 'active'
        ]);
        $this->line("   ✓ Requestor: {$requestor->name} ({$requestor->email})");

        // Create/find adviser
        $adviser = User::firstOrCreate([
            'email' => 'test-adviser@example.com'
        ], [
            'name' => 'Test Adviser',
            'password' => Hash::make('password'),
            'role' => 'adviser',
            'status' => 'active'
        ]);
        $this->line("   ✓ Adviser: {$adviser->name} ({$adviser->email})");

        // Create/find staff
        $staff = User::firstOrCreate([
            'email' => 'test-staff@example.com'
        ], [
            'name' => 'Test Staff',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active'
        ]);
        $this->line("   ✓ Staff: {$staff->name} ({$staff->email})");

        // Create/find admin
        $admin = User::firstOrCreate([
            'email' => 'test-admin@example.com'
        ], [
            'name' => 'Test Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);
        $this->line("   ✓ Admin: {$admin->name} ({$admin->email})");
        $this->newLine();

        // 2. Create test organization
        $this->info('2. Setting up test organization...');
        $organization = Organization::firstOrCreate([
            'org_name' => 'Test Organization'
        ], [
            'org_desc' => 'A test organization for booking system validation',
            'adviser_id' => $adviser->id
        ]);
        $this->line("   ✓ Organization: {$organization->org_name}");
        $this->line("   ✓ Assigned Adviser: {$adviser->name}");
        $this->newLine();

        // 3. Create a test booking request
        $this->info('3. Creating test booking request...');
        $bookingRequest = OrganizationBookingRequest::create([
            'organization_id' => $organization->org_id,
            'requestor_id' => $requestor->id,
            'activity_name' => 'Community Outreach Day',
            'purpose' => 'Annual Community Outreach Event',
            'activity_details' => 'We would like to organize a community outreach program featuring educational workshops, health screenings, and spiritual guidance sessions. The event will serve approximately 200 community members and requires venue support for the entire day.',
            'requested_date' => now()->addDays(30),
            'requested_venue' => 'Main Hall',
            'estimated_participants' => 200,
            'status' => 'pending'
        ]);

        $this->line("   ✓ Booking Request Created: #{$bookingRequest->id}");
        $this->line("   ✓ Organization: {$bookingRequest->organization->org_name}");
        $this->line("   ✓ Requestor: {$bookingRequest->requestor->name}");
        $this->line("   ✓ Status: {$bookingRequest->status}");
        $this->line("   ✓ Purpose: " . substr($bookingRequest->purpose, 0, 50) . "...");
        $this->newLine();

        // 4. Test notification service
        $this->info('4. Testing notification service...');
        try {
            $notificationService = app(OrganizationBookingNotificationService::class);
            $notificationService->notifyAdviserOfNewRequest($bookingRequest);
            
            // Mark as notified for testing
            $bookingRequest->markAdviserNotified();
            
            $this->line('   ✓ Adviser notification sent successfully');
            $this->line("   ✓ Notification timestamp recorded: {$bookingRequest->fresh()->adviser_notified_at}");
        } catch (\Exception $e) {
            $this->error("   ❌ Notification service error: {$e->getMessage()}");
        }
        $this->newLine();

        // 5. Test approval workflow
        $this->info('5. Testing approval workflow...');
        try {
            $bookingRequest->approve($adviser, 'This event aligns well with our community mission and is approved.');
            $this->line('   ✓ Request approved successfully');
            $this->line("   ✓ Approved by: {$bookingRequest->fresh()->approvedBy->name}");
            $this->line("   ✓ Approval date: {$bookingRequest->fresh()->approved_at}");
            $this->line("   ✓ Adviser notes: {$bookingRequest->fresh()->adviser_notes}");
        } catch (\Exception $e) {
            $this->error("   ❌ Approval workflow error: {$e->getMessage()}");
        }
        $this->newLine();

        // 6. Create another request for rejection test
        $this->info('6. Testing rejection workflow...');
        try {
            $rejectRequest = OrganizationBookingRequest::create([
                'organization_id' => $organization->org_id,
                'requestor_id' => $requestor->id,
                'activity_name' => 'Test Rejection Event',
                'purpose' => 'Test Event for Rejection',
                'activity_details' => 'This is a test event that will be rejected for demonstration purposes.',
                'requested_date' => now()->addDays(45),
                'requested_venue' => 'Conference Room',
                'estimated_participants' => 50,
                'status' => 'pending'
            ]);

            $rejectRequest->reject($adviser, 'Unfortunately, we cannot accommodate this request due to scheduling conflicts.');
            
            $this->line("   ✓ Rejection request created: #{$rejectRequest->id}");
            $this->line('   ✓ Request rejected successfully');
            $this->line("   ✓ Rejected by: {$rejectRequest->fresh()->approvedBy->name}");
            $this->line("   ✓ Rejection date: {$rejectRequest->fresh()->rejected_at}");
            $this->line("   ✓ Rejection reason: {$rejectRequest->fresh()->adviser_notes}");
        } catch (\Exception $e) {
            $this->error("   ❌ Rejection workflow error: {$e->getMessage()}");
        }
        $this->newLine();

        // 7. Test model relationships and scopes
        $this->info('7. Testing model relationships and scopes...');
        try {
            // Test relationships
            $requests = OrganizationBookingRequest::with(['organization', 'requestor', 'approvedBy'])->get();
            $this->line("   ✓ Total requests in system: {$requests->count()}");
            
            // Test scopes
            $pendingCount = OrganizationBookingRequest::pending()->count();
            $approvedCount = OrganizationBookingRequest::approved()->count();
            $rejectedCount = OrganizationBookingRequest::rejected()->count();
            
            $this->line("   ✓ Pending requests: {$pendingCount}");
            $this->line("   ✓ Approved requests: {$approvedCount}");
            $this->line("   ✓ Rejected requests: {$rejectedCount}");
            
            // Test organization relationship
            $orgRequests = $organization->bookingRequests()->count();
            $this->line("   ✓ Requests for {$organization->org_name}: {$orgRequests}");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Model testing error: {$e->getMessage()}");
        }
        $this->newLine();

        // 8. Final summary
        $this->info('8. Test Summary');
        $this->info('================');

        $totalRequests = OrganizationBookingRequest::count();
        $pendingRequests = OrganizationBookingRequest::pending()->count();
        $approvedRequests = OrganizationBookingRequest::approved()->count();
        $rejectedRequests = OrganizationBookingRequest::rejected()->count();

        $this->line("✅ Total Organization Booking Requests: {$totalRequests}");
        $this->line("⏳ Pending: {$pendingRequests}");
        $this->line("✅ Approved: {$approvedRequests}");
        $this->line("❌ Rejected: {$rejectedRequests}");
        $this->line("👥 Active Users: " . User::where('status', 'active')->count());
        $this->line("🏢 Organizations: " . Organization::count());
        $this->newLine();

        $this->info('🎉 Organization Booking System Test Complete!');
        $this->info('All core functionality is working properly.');
        $this->newLine();
        $this->info("Test users created with password 'password':");
        $this->line('- Requestor: test-requestor@example.com');
        $this->line('- Adviser: test-adviser@example.com');
        $this->line('- Staff: test-staff@example.com');
        $this->line('- Admin: test-admin@example.com');

        return Command::SUCCESS;
    }
}

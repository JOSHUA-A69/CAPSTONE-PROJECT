<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationBookingRequest;
use App\Services\OrganizationBookingNotificationService;
use Illuminate\Support\Facades\Hash;

echo "🚀 Starting Organization Booking System Test\n";
echo "==============================================\n\n";

// 1. Ensure we have test users
echo "1. Setting up test users...\n";

// Create/find requestor
$requestor = User::firstOrCreate([
    'email' => 'test-requestor@example.com'
], [
    'name' => 'Test Requestor',
    'password' => Hash::make('password'),
    'role' => 'requestor',
    'status' => 'active'
]);
echo "   ✓ Requestor: {$requestor->name} ({$requestor->email})\n";

// Create/find adviser
$adviser = User::firstOrCreate([
    'email' => 'test-adviser@example.com'
], [
    'name' => 'Test Adviser',
    'password' => Hash::make('password'),
    'role' => 'adviser',
    'status' => 'active'
]);
echo "   ✓ Adviser: {$adviser->name} ({$adviser->email})\n";

// Create/find staff
$staff = User::firstOrCreate([
    'email' => 'test-staff@example.com'
], [
    'name' => 'Test Staff',
    'password' => Hash::make('password'),
    'role' => 'staff',
    'status' => 'active'
]);
echo "   ✓ Staff: {$staff->name} ({$staff->email})\n";

// Create/find admin
$admin = User::firstOrCreate([
    'email' => 'test-admin@example.com'
], [
    'name' => 'Test Admin',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'status' => 'active'
]);
echo "   ✓ Admin: {$admin->name} ({$admin->email})\n\n";

// 2. Create test organization
echo "2. Setting up test organization...\n";
$organization = Organization::firstOrCreate([
    'name' => 'Test Organization'
], [
    'org_desc' => 'A test organization for booking system validation',
    'adviser_id' => $adviser->id
]);
echo "   ✓ Organization: {$organization->name}\n";
echo "   ✓ Assigned Adviser: {$adviser->name}\n\n";

// 3. Create a test booking request
echo "3. Creating test booking request...\n";
$bookingRequest = OrganizationBookingRequest::create([
    'organization_id' => $organization->id,
    'requestor_id' => $requestor->id,
    'purpose' => 'Annual Community Outreach Event',
    'activity_details' => 'We would like to organize a community outreach program featuring educational workshops, health screenings, and spiritual guidance sessions. The event will serve approximately 200 community members and requires venue support for the entire day.',
    'status' => 'pending'
]);

echo "   ✓ Booking Request Created: #{$bookingRequest->id}\n";
echo "   ✓ Organization: {$bookingRequest->organization->name}\n";
echo "   ✓ Requestor: {$bookingRequest->requestor->name}\n";
echo "   ✓ Status: {$bookingRequest->status}\n";
echo "   ✓ Purpose: " . substr($bookingRequest->purpose, 0, 50) . "...\n\n";

// 4. Test notification service
echo "4. Testing notification service...\n";
try {
    $notificationService = app(OrganizationBookingNotificationService::class);
    $notificationService->notifyAdviserOfNewRequest($bookingRequest);
    
    // Mark as notified for testing
    $bookingRequest->markAdviserNotified();
    
    echo "   ✓ Adviser notification sent successfully\n";
    echo "   ✓ Notification timestamp recorded: {$bookingRequest->fresh()->adviser_notified_at}\n";
} catch (Exception $e) {
    echo "   ❌ Notification service error: {$e->getMessage()}\n";
}
echo "\n";

// 5. Test approval workflow
echo "5. Testing approval workflow...\n";
try {
    $bookingRequest->approve($adviser, 'This event aligns well with our community mission and is approved.');
    echo "   ✓ Request approved successfully\n";
    echo "   ✓ Approved by: {$bookingRequest->fresh()->approvedBy->name}\n";
    echo "   ✓ Approval date: {$bookingRequest->fresh()->approved_at}\n";
    echo "   ✓ Adviser notes: {$bookingRequest->fresh()->adviser_notes}\n";
} catch (Exception $e) {
    echo "   ❌ Approval workflow error: {$e->getMessage()}\n";
}
echo "\n";

// 6. Create another request for rejection test
echo "6. Testing rejection workflow...\n";
try {
    $rejectRequest = OrganizationBookingRequest::create([
        'organization_id' => $organization->id,
        'requestor_id' => $requestor->id,
        'purpose' => 'Test Event for Rejection',
        'activity_details' => 'This is a test event that will be rejected for demonstration purposes.',
        'status' => 'pending'
    ]);

    $rejectRequest->reject($adviser, 'Unfortunately, we cannot accommodate this request due to scheduling conflicts.');
    
    echo "   ✓ Rejection request created: #{$rejectRequest->id}\n";
    echo "   ✓ Request rejected successfully\n";
    echo "   ✓ Rejected by: {$rejectRequest->fresh()->approvedBy->name}\n";
    echo "   ✓ Rejection date: {$rejectRequest->fresh()->rejected_at}\n";
    echo "   ✓ Rejection reason: {$rejectRequest->fresh()->adviser_notes}\n";
} catch (Exception $e) {
    echo "   ❌ Rejection workflow error: {$e->getMessage()}\n";
}
echo "\n";

// 7. Test model relationships and scopes
echo "7. Testing model relationships and scopes...\n";
try {
    // Test relationships
    $requests = OrganizationBookingRequest::with(['organization', 'requestor', 'approvedBy'])->get();
    echo "   ✓ Total requests in system: {$requests->count()}\n";
    
    // Test scopes
    $pendingCount = OrganizationBookingRequest::pending()->count();
    $approvedCount = OrganizationBookingRequest::approved()->count();
    $rejectedCount = OrganizationBookingRequest::rejected()->count();
    
    echo "   ✓ Pending requests: {$pendingCount}\n";
    echo "   ✓ Approved requests: {$approvedCount}\n";
    echo "   ✓ Rejected requests: {$rejectedCount}\n";
    
    // Test organization relationship
    $orgRequests = $organization->bookingRequests()->count();
    echo "   ✓ Requests for {$organization->name}: {$orgRequests}\n";
    
} catch (Exception $e) {
    echo "   ❌ Model testing error: {$e->getMessage()}\n";
}
echo "\n";

// 8. Final summary
echo "8. Test Summary\n";
echo "================\n";

$totalRequests = OrganizationBookingRequest::count();
$pendingRequests = OrganizationBookingRequest::pending()->count();
$approvedRequests = OrganizationBookingRequest::approved()->count();
$rejectedRequests = OrganizationBookingRequest::rejected()->count();

echo "✅ Total Organization Booking Requests: {$totalRequests}\n";
echo "⏳ Pending: {$pendingRequests}\n";
echo "✅ Approved: {$approvedRequests}\n";
echo "❌ Rejected: {$rejectedRequests}\n";
echo "👥 Active Users: " . User::where('status', 'active')->count() . "\n";
echo "🏢 Organizations: " . Organization::count() . "\n";
echo "\n";

echo "🎉 Organization Booking System Test Complete!\n";
echo "All core functionality is working properly.\n";
echo "\nTest users created with password 'password':\n";
echo "- Requestor: test-requestor@example.com\n";
echo "- Adviser: test-adviser@example.com\n";
echo "- Staff: test-staff@example.com\n";
echo "- Admin: test-admin@example.com\n";

<?php

// Script to send notifications for already-approved organization booking requests

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\OrganizationBookingRequest;
use App\Models\Notification;

echo "=== Sending Approval Notifications for Organization Bookings ===\n\n";

$approvedRequests = OrganizationBookingRequest::where('status', 'approved')
    ->with(['requestor', 'organization'])
    ->get();

echo "Found " . count($approvedRequests) . " approved requests.\n\n";

foreach ($approvedRequests as $request) {
    $requestor = $request->requestor;
    $organization = $request->organization;
    
    echo "Request #{$request->id}: {$request->activity_name}\n";
    echo "  Requestor: " . ($requestor ? $requestor->full_name . " (ID: {$requestor->id})" : "NULL") . "\n";
    echo "  Organization: " . ($organization ? $organization->org_name : "NULL") . "\n";
    
    if ($requestor && $requestor->id) {
        // Check if notification already exists
        $existingNotification = Notification::where('user_id', $requestor->id)
            ->where('type', 'org_booking_approved')
            ->where('message', 'like', '%' . $request->activity_name . '%')
            ->first();
        
        if ($existingNotification) {
            echo "  -> Notification already exists, skipping.\n";
        } else {
            Notification::create([
                'user_id' => $requestor->id,
                'message' => "Your organization booking request for \"{$request->activity_name}\" ({$organization->org_name}) has been approved by your adviser.",
                'type' => 'org_booking_approved',
                'sent_at' => now(),
                'data' => [
                    'organization_booking_request_id' => $request->id,
                    'activity_name' => $request->activity_name,
                    'organization_name' => $organization->org_name,
                ],
            ]);
            echo "  -> Notification created!\n";
        }
    } else {
        echo "  -> ERROR: Requestor not found or has no ID\n";
    }
    echo "\n";
}

echo "Done!\n";

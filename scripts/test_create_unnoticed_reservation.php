<?php

use App\Models\Reservation;
use App\Models\User;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Venue;

// Get data
$requestor = User::where('role', 'requestor')->first();
$adviser = User::where('role', 'adviser')->first();
$organization = Organization::first();
$service = Service::first();
$venue = Venue::first();

if (!$requestor || !$adviser || !$organization || !$service || !$venue) {
    echo "Missing required data:\n";
    echo "Requestor: " . ($requestor ? 'found' : 'NOT FOUND') . "\n";
    echo "Adviser: " . ($adviser ? 'found' : 'NOT FOUND') . "\n";
    echo "Organization: " . ($organization ? 'found' : 'NOT FOUND') . "\n";
    echo "Service: " . ($service ? 'found' : 'NOT FOUND') . "\n";
    echo "Venue: " . ($venue ? 'found' : 'NOT FOUND') . "\n";
    exit(1);
}

// Create test reservation
$reservation = Reservation::create([
    'user_id' => $requestor->id,
    'service_id' => $service->id,
    'org_id' => $organization->id,
    'venue_id' => $venue->id,
    'status' => 'pending',
    'schedule_date' => now()->addDays(7),
    'created_at' => now()->subDays(2),
]);

echo "✅ Created test reservation #{$reservation->reservation_id}\n";
echo "   Requestor: {$requestor->first_name} {$requestor->last_name}\n";
echo "   Service: {$service->service_name}\n";
echo "   Organization: {$organization->org_name}\n";
echo "   Status: {$reservation->status}\n";
echo "   Created: {$reservation->created_at}\n";

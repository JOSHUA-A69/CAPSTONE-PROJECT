<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Get a requestor
$requestor = User::where('role', 'requestor')->first();
if (!$requestor) {
    echo "No requestor found!\n";
    exit(1);
}

// Get an adviser (preferably one with an organization)
$adviser = User::where('role', 'adviser')->first();
if (!$adviser) {
    echo "No adviser found!\n";
    exit(1);
}

// Get an organization
$organization = \App\Models\Organization::first();
if (!$organization) {
    echo "No organization found!\n";
    exit(1);
}

// Get a service
$service = \App\Models\Service::first();
if (!$service) {
    echo "No service found!\n";
    exit(1);
}

// Get a venue
$venue = \App\Models\Venue::first();
if (!$venue) {
    echo "No venue found!\n";
    exit(1);
}

// Create a reservation that's > 24 hours old and pending
$reservation = Reservation::create([
    'user_id' => $requestor->id,
    'service_id' => $service->id,
    'org_id' => $organization->id,
    'venue_id' => $venue->id,
    'status' => 'pending',
    'schedule_date' => now()->addDays(7),
    'created_at' => now()->subDays(2), // 2 days old
    'adviser_responded_at' => null,
    'staff_followed_up_at' => null,
]);

echo "Created test reservation #{$reservation->reservation_id}\n";
echo "Requestor: {$requestor->first_name} {$requestor->last_name}\n";
echo "Service: {$service->service_name}\n";
echo "Organization: {$organization->org_name}\n";
echo "Status: {$reservation->status}\n";
echo "Created at: {$reservation->created_at}\n\n";

// Now run the command
echo "Running: php artisan reservations:check-unnoticed --send-notifications\n\n";
\Illuminate\Support\Facades\Artisan::call('reservations:check-unnoticed', ['--send-notifications' => true]);
echo \Illuminate\Support\Facades\Artisan::output();

// Check notifications were created
$staffNotifications = DB::table('notifications')
    ->where('type', 'Urgent')
    ->where('reservation_id', $reservation->reservation_id)
    ->get();

echo "\nNotifications created: " . $staffNotifications->count() . "\n";
foreach ($staffNotifications as $notif) {
    echo "- User {$notif->user_id}: {$notif->message}\n";
}

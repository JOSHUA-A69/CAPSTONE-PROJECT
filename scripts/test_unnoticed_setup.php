<?php

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Get a requestor
$requestor = User::where('role', 'requestor')->first();
if (!$requestor) {
    dd('No requestor found');
}

// Get an organization (this will automatically link to adviser)
$organization = \App\Models\Organization::with('adviser')->first();
if (!$organization || !$organization->adviser) {
    dd('No organization with adviser found');
}

// Get a service
$service = DB::table('services')->first();
if (!$service) {
    dd('No service found');
}

// Get a venue
$venue = DB::table('venues')->first();
if (!$venue) {
    dd('No venue found');
}

// Create a reservation that's > 24 hours old and pending
try {
    $reservation = Reservation::create([
        'user_id' => $requestor->id,
        'service_id' => $service->id,
        'org_id' => $organization->id,
        'venue_id' => $venue->id,
        'status' => 'pending',
        'schedule_date' => now()->addDays(7),
        'created_at' => now()->subDays(2), // 2 days old
        'updated_at' => now()->subDays(2),
    ]);

    dd([
        'Created reservation' => $reservation->reservation_id,
        'Requestor' => $requestor->first_name . ' ' . $requestor->last_name,
        'Organization' => $organization->org_name,
        'Adviser' => $organization->adviser->full_name ?? 'Unknown',
        'created_at' => $reservation->created_at,
    ]);
} catch (\Exception $e) {
    dd('Error creating reservation: ' . $e->getMessage());
}

#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OrganizationBookingRequest;
use App\Models\User;
use App\Models\Organization;

function line($msg = '') { echo $msg . "\n"; }

line("");
line("╔═══════════════════════════════════════════════════════════╗");
line("║  Create Org Booking Test (Jan 10) - eReligiousServices   ║");
line("╚═══════════════════════════════════════════════════════════╝");
line("");

$org = Organization::first();
$requestor = User::where('role', 'requestor')->where('status', 'active')->first() 
    ?? User::where('role', 'requestor')->first();

if (!$org || !$requestor) {
    line("❌ Missing required data:");
    line("   - organization: " . (!!$org ? 'OK' : 'MISSING'));
    line("   - requestor:    " . (!!$requestor ? 'OK' : 'MISSING'));
    exit(1);
}

$dt = \Carbon\Carbon::create(2026, 1, 10, 9, 0, 0, config('app.timezone'));

$booking = OrganizationBookingRequest::create([
    'organization_id' => $org->org_id,
    'requestor_id' => $requestor->id,
    'activity_name' => 'Community Outreach',
    'purpose' => 'Outreach and engagement',
    'requested_date' => $dt,
    'requested_venue' => 'Church Hall',
    'estimated_participants' => 50,
    'special_requirements' => 'Projector and PA system',
    'status' => 'pending',
    'submitted_at' => now(),
    'adviser_notified_at' => now(),
]);

line("✅ Created Organization Booking Request #{$booking->id}");
line("   Org:        " . optional($booking->organization)->org_name);
line("   Requestor:  " . optional($booking->requestor)->full_name ?? optional($booking->requestor)->name);
line("   When:       " . $booking->requested_date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'));
line("   Status:     " . $booking->status);
line("");

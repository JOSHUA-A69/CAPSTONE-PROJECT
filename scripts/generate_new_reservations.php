#!/usr/bin/env php
<?php

/**
 * Generate fresh test reservations and drive them through core workflow steps
 * so comprehensive_test.php reflects new activity.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Reservation;
use App\Models\ReservationHistory;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Venue;
use App\Services\ReservationNotificationService;
use Illuminate\Support\Str;

function line($msg = '') { echo $msg . "\n"; }

line("");
line("╔════════════════════════════════════════════════════════════╗");
line("║     GENERATE NEW TEST RESERVATIONS - eReligiousServices    ║");
line("╚════════════════════════════════════════════════════════════╝");
line("");

$now = now();
$created = [];

// Preconditions: core reference data and role users
$requestor = User::where('role', 'requestor')->where('status', 'active')->first();
$adviser   = User::where('role', 'adviser')->where('status', 'active')->first();
$priest    = User::where('role', 'priest')->where('status', 'active')->first();
$admin     = User::whereIn('role', ['admin','staff'])->where('status', 'active')->first();
$org       = Organization::first();
$service   = Service::inRandomOrder()->first();
$venue     = Venue::first();

if (!$requestor || !$adviser || !$priest || !$admin || !$org || !$service) {
    line("❌ Missing required seed data. Ensure seeders ran and role users exist.");
    line("   - requestor: " . (!!$requestor ? 'OK' : 'MISSING'));
    line("   - adviser:   " . (!!$adviser ? 'OK' : 'MISSING'));
    line("   - priest:    " . (!!$priest ? 'OK' : 'MISSING'));
    line("   - admin/staff:" . (!!$admin ? 'OK' : 'MISSING'));
    line("   - organization:" . (!!$org ? 'OK' : 'MISSING'));
    line("   - service:   " . (!!$service ? 'OK' : 'MISSING'));
    exit(1);
}

$notify = new ReservationNotificationService();

// Helper to create a base reservation
$makeReservation = function (string $label, int $daysAhead) use ($requestor, $org, $service, $venue, $now) {
    $dt = $now->copy()->addDays($daysAhead)->setTime(rand(8,16), [0,15,30,45][array_rand([0,1,2,3])]);
    $reservation = Reservation::create([
        'user_id' => $requestor->id,
        'org_id' => $org->org_id,
        'venue_id' => optional($venue)->venue_id,
        'service_id' => $service->service_id,
        'schedule_date' => $dt,
        'status' => 'pending',
        'purpose' => $label,
        'details' => 'Automated test reservation generated on ' . $now->toDateTimeString(),
        'participants_count' => rand(10, 80),
    ]);

    ReservationHistory::create([
        'reservation_id' => $reservation->reservation_id,
        'performed_by' => $requestor->id,
        'action' => 'created',
        'remarks' => 'Auto-created test reservation',
        'performed_at' => now(),
    ]);

    ReservationHistory::create([
        'reservation_id' => $reservation->reservation_id,
        'performed_by' => $requestor->id,
        'action' => 'submitted',
        'remarks' => 'Request submitted by requestor',
        'performed_at' => now(),
    ]);

    return $reservation;
};

// R1: Full happy path to approved (priest confirmed)
$r1 = $makeReservation('Test Run R1 - Full Flow', 3);
$created[] = $r1->reservation_id;

// Notify submission (requestor/adviser)
$notify->notifyReservationSubmitted($r1);
$r1->adviser_notified_at = now();
$r1->save();
ReservationHistory::create([
    'reservation_id' => $r1->reservation_id,
    'performed_by' => $r1->user_id,
    'action' => 'adviser_notified',
    'remarks' => 'Adviser notified of submission',
    'performed_at' => now(),
]);

// Adviser approves
$r1->status = 'adviser_approved';
$r1->adviser_responded_at = now();
$r1->save();
ReservationHistory::create([
    'reservation_id' => $r1->reservation_id,
    'performed_by' => $r1->user_id,
    'action' => 'adviser_approved',
    'remarks' => 'Approved by adviser (auto)',
    'performed_at' => now(),
]);
$notify->notifyAdviserApproved($r1, 'Auto-approved for testing.');

// Admin assigns priest
$r1->officiant_id = $priest->id;
$r1->admin_notified_at = now();
$r1->status = 'admin_approved';
$r1->save();
ReservationHistory::create([
    'reservation_id' => $r1->reservation_id,
    'performed_by' => $admin->id,
    'action' => 'priest_assigned',
    'remarks' => 'Priest assigned (auto)',
    'performed_at' => now(),
]);
$notify->notifyPriestAssigned($r1);

// Priest confirms
$r1->priest_confirmation = 'confirmed';
$r1->priest_confirmed_at = now();
$r1->status = 'approved';
$r1->save();
ReservationHistory::create([
    'reservation_id' => $r1->reservation_id,
    'performed_by' => $priest->id,
    'action' => 'priest_confirmed',
    'remarks' => 'Priest availability confirmed (auto)',
    'performed_at' => now(),
]);

// R2: Pending only (left for adviser review)
$r2 = $makeReservation('Test Run R2 - Pending', 5);
$created[] = $r2->reservation_id;
$notify->notifyReservationSubmitted($r2);
$r2->adviser_notified_at = now();
$r2->save();
ReservationHistory::create([
    'reservation_id' => $r2->reservation_id,
    'performed_by' => $r2->user_id,
    'action' => 'adviser_notified',
    'remarks' => 'Adviser notified of submission',
    'performed_at' => now(),
]);

// R3: Adviser rejects
$r3 = $makeReservation('Test Run R3 - Adviser Rejected', 7);
$created[] = $r3->reservation_id;
$notify->notifyReservationSubmitted($r3);
$r3->adviser_notified_at = now();
$r3->save();
ReservationHistory::create([
    'reservation_id' => $r3->reservation_id,
    'performed_by' => $r3->user_id,
    'action' => 'adviser_notified',
    'remarks' => 'Adviser notified of submission',
    'performed_at' => now(),
]);

// Reject and notify
$r3->status = 'rejected';
$r3->adviser_responded_at = now();
$r3->save();
ReservationHistory::create([
    'reservation_id' => $r3->reservation_id,
    'performed_by' => $adviser->id,
    'action' => 'adviser_rejected',
    'remarks' => 'Rejected by adviser (auto test case)',
    'performed_at' => now(),
]);
$notify->notifyAdviserRejected($r3, 'Auto-rejected for testing.');

line("");
line("✅ Created new reservations: " . implode(', ', $created));
line("   - R1 approved, R2 pending, R3 rejected");
line("");

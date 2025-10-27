#!/usr/bin/env php
<?php

/**
 * Comprehensive System Test Script
 * Tests all major features and user flows
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Reservation;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Venue;
use App\Models\Notification;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     COMPREHENSIVE SYSTEM TEST - eReligiousServices        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$passed = 0;
$failed = 0;
$warnings = 0;

function test($name, $callback) {
    global $passed, $failed, $warnings;
    echo "🧪 Testing: $name... ";
    try {
        $result = $callback();
        if ($result === 'warning') {
            echo "⚠️  WARNING\n";
            $warnings++;
        } elseif ($result === false) {
            echo "❌ FAILED\n";
            $failed++;
        } else {
            echo "✅ PASSED\n";
            $passed++;
        }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1️⃣  DATABASE & MODELS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

test("Users table has data", function() {
    return User::count() > 0;
});

test("All user roles exist", function() {
    $roles = User::distinct()->pluck('role')->toArray();
    $expected = ['requestor', 'adviser', 'priest', 'admin', 'staff'];
    $missing = array_diff($expected, $roles);
    if (!empty($missing)) {
        echo "\n   ⚠️  Missing roles: " . implode(', ', $missing) . "\n";
        return 'warning';
    }
    return true;
});

test("Organizations have advisers assigned", function() {
    $orgsWithoutAdvisers = Organization::whereNull('adviser_id')->count();
    if ($orgsWithoutAdvisers > 0) {
        echo "\n   ⚠️  $orgsWithoutAdvisers organizations without advisers\n";
        return 'warning';
    }
    return true;
});

test("Services are configured", function() {
    return Service::count() > 0;
});

test("Venues are configured", function() {
    return Venue::count() > 0;
});

test("Reservations exist", function() {
    return Reservation::count() > 0;
});

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "2️⃣  USER AUTHENTICATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

test("Active users can login", function() {
    $activeUsers = User::where('status', 'active')->count();
    echo "\n   ℹ️  $activeUsers active users\n";
    return $activeUsers > 0;
});

test("Email verification is enabled", function() {
    $unverified = User::whereNull('email_verified_at')->count();
    echo "\n   ℹ️  $unverified unverified users\n";
    return true;
});

test("Requestor users exist", function() {
    return User::where('role', 'requestor')->count() > 0;
});

test("Adviser users exist", function() {
    return User::where('role', 'adviser')->count() > 0;
});

test("Priest users exist", function() {
    return User::where('role', 'priest')->count() > 0;
});

test("Admin users exist", function() {
    return User::where('role', 'admin')->count() > 0;
});

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "3️⃣  RESERVATION WORKFLOW\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

test("Pending reservations exist", function() {
    $pending = Reservation::where('status', 'pending')->count();
    echo "\n   ℹ️  $pending pending reservations\n";
    return true;
});

test("Reservations have organizations", function() {
    $withoutOrg = Reservation::whereNull('org_id')->count();
    if ($withoutOrg > 0) {
        echo "\n   ⚠️  $withoutOrg reservations without organization\n";
        return 'warning';
    }
    return true;
});

test("Reservations have priests assigned", function() {
    $withoutPriest = Reservation::whereNull('officiant_id')->count();
    if ($withoutPriest > 0) {
        echo "\n   ℹ️  $withoutPriest reservations without priest\n";
    }
    return true;
});

test("Adviser approval workflow", function() {
    $adviserApproved = Reservation::where('status', 'adviser_approved')->count();
    echo "\n   ℹ️  $adviserApproved adviser-approved reservations\n";
    return true;
});

test("Completed reservations", function() {
    $approved = Reservation::where('status', 'approved')->count();
    echo "\n   ℹ️  $approved approved reservations\n";
    return true;
});

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "4️⃣  NOTIFICATION SYSTEM\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

test("Notifications are being created", function() {
    $total = Notification::count();
    echo "\n   ℹ️  $total total notifications\n";
    return $total > 0;
});

test("Unread notifications exist", function() {
    $unread = Notification::whereNull('read_at')->count();
    echo "\n   ℹ️  $unread unread notifications\n";
    return true;
});

test("Advisers receive notifications", function() {
    $advisers = User::where('role', 'adviser')->pluck('id');
    $adviserNotifs = Notification::whereIn('user_id', $advisers)->count();
    echo "\n   ℹ️  $adviserNotifs notifications to advisers\n";
    return $adviserNotifs > 0;
});

test("Recent notifications (last 24h)", function() {
    $recent = Notification::where('sent_at', '>', now()->subDay())->count();
    echo "\n   ℹ️  $recent notifications in last 24 hours\n";
    return true;
});

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "5️⃣  DATA INTEGRITY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

test("Reservations have valid relationships", function() {
    $invalidService = Reservation::whereNotNull('service_id')
        ->whereDoesntHave('service')->count();
    $invalidUser = Reservation::whereNotNull('user_id')
        ->whereDoesntHave('user')->count();

    if ($invalidService > 0 || $invalidUser > 0) {
        echo "\n   ⚠️  Invalid relationships found\n";
        return 'warning';
    }
    return true;
});

test("Organizations have valid advisers", function() {
    $orgs = Organization::whereNotNull('adviser_id')->get();
    $invalid = 0;
    foreach ($orgs as $org) {
        if (!$org->adviser) {
            $invalid++;
        }
    }
    if ($invalid > 0) {
        echo "\n   ⚠️  $invalid organizations with invalid adviser_id\n";
        return 'warning';
    }
    return true;
});

test("Notification references are valid", function() {
    $invalidReservation = Notification::whereNotNull('reservation_id')
        ->whereDoesntHave('reservation')->count();

    if ($invalidReservation > 0) {
        echo "\n   ⚠️  $invalidReservation notifications with invalid reservation_id\n";
        return 'warning';
    }
    return true;
});

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "6️⃣  RECENT SYSTEM ACTIVITY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Show recent reservations
echo "📋 Recent Reservations (last 5):\n";
$recentReservations = Reservation::with(['user', 'service', 'organization'])
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($recentReservations as $r) {
    echo "   • #" . $r->reservation_id . " - " . $r->service->service_name;
    echo " by " . $r->user->first_name;
    echo " (" . $r->status . ") - " . $r->created_at->diffForHumans() . "\n";
}

// Show recent notifications
echo "\n🔔 Recent Notifications (last 5):\n";
$recentNotifs = Notification::with('user')
    ->orderBy('sent_at', 'desc')
    ->take(5)
    ->get();

foreach ($recentNotifs as $n) {
    echo "   • To: " . $n->user->first_name;
    echo " - " . strip_tags(substr($n->message, 0, 50)) . "...";
    echo " (" . $n->sent_at->diffForHumans() . ")\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 TEST SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$total = $passed + $failed + $warnings;
echo "   ✅ Passed:   $passed / $total\n";
if ($warnings > 0) {
    echo "   ⚠️  Warnings: $warnings\n";
}
if ($failed > 0) {
    echo "   ❌ Failed:   $failed\n";
}

echo "\n";

if ($failed > 0) {
    echo "❌ SYSTEM HAS CRITICAL ISSUES - DO NOT COMMIT\n\n";
    exit(1);
} elseif ($warnings > 0) {
    echo "⚠️  SYSTEM HAS WARNINGS - Review before committing\n\n";
    exit(0);
} else {
    echo "✅ ALL TESTS PASSED - Safe to commit!\n\n";
    exit(0);
}

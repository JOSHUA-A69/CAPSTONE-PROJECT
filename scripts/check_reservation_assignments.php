<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Reservation;
use App\Models\User;

echo "🔍 CHECKING RESERVATION ASSIGNMENT STATUS\n";
echo "==========================================\n\n";

// Get all reservations
$allReservations = Reservation::with(['user', 'service', 'officiant'])->get();

echo "📊 Total Reservations in System: " . $allReservations->count() . "\n\n";

if ($allReservations->isEmpty()) {
    echo "❌ No reservations found in the system!\n";
    echo "💡 This explains why priests have 0 pending confirmations.\n";
    echo "   Create a reservation first, then have an admin assign a priest to it.\n";
    exit;
}

// Group reservations by status
$byStatus = $allReservations->groupBy('status');

echo "📋 Reservations by Status:\n";
foreach ($byStatus as $status => $reservations) {
    echo "   • {$status}: " . $reservations->count() . "\n";
}

echo "\n";

// Check reservations awaiting priest assignment
$needsPriestAssignment = Reservation::whereNull('officiant_id')
    ->whereIn('status', ['pending', 'adviser_approved'])
    ->with(['user', 'service'])
    ->get();

if ($needsPriestAssignment->count() > 0) {
    echo "⏳ RESERVATIONS AWAITING PRIEST ASSIGNMENT: " . $needsPriestAssignment->count() . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($needsPriestAssignment as $res) {
        echo "   Reservation #{$res->reservation_id}\n";
        echo "   Requestor: {$res->user->first_name} {$res->user->last_name}\n";
        echo "   Service: {$res->service->service_name}\n";
        echo "   Status: {$res->status}\n";
        echo "   Schedule: {$res->schedule_date}\n";
        echo "   💡 Admin needs to assign a priest to this reservation\n\n";
    }
}

// Check reservations assigned to priests
$assignedToPriests = Reservation::whereNotNull('officiant_id')
    ->with(['officiant', 'service'])
    ->get();

if ($assignedToPriests->count() > 0) {
    echo "\n👤 RESERVATIONS ASSIGNED TO PRIESTS: " . $assignedToPriests->count() . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($assignedToPriests as $res) {
        echo "   Reservation #{$res->reservation_id}\n";
        echo "   Priest: {$res->officiant->first_name} {$res->officiant->last_name}\n";
        echo "   Service: {$res->service->service_name}\n";
        echo "   Status: {$res->status}\n";
        echo "   Priest Confirmation: " . ($res->priest_confirmation ?? 'NULL') . "\n";
        echo "   Notified At: " . ($res->priest_notified_at ? $res->priest_notified_at->format('Y-m-d H:i:s') : 'NULL') . "\n\n";
    }
}

if ($needsPriestAssignment->count() == 0 && $assignedToPriests->count() == 0) {
    echo "ℹ️  All reservations are either completed, cancelled, or external priests\n";
}

echo "\n📝 HOW TO TEST NOTIFICATION SYSTEM:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Create a new reservation as a requestor\n";
echo "2. Have an adviser approve it (if required)\n";
echo "3. Have an admin assign a priest to the reservation\n";
echo "4. The priest should receive:\n";
echo "   ✓ In-app notification\n";
echo "   ✓ Email notification  \n";
echo "   ✓ SMS notification (if phone number configured)\n";
echo "5. Check the priest's dashboard - it should show the pending confirmation\n";


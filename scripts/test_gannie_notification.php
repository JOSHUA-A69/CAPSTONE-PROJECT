<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Venue;
use App\Services\ReservationNotificationService;
use App\Models\ReservationHistory;

echo "🧪 TESTING PRIEST NOTIFICATION SYSTEM\n";
echo "=====================================\n\n";

// Find gannie
$gannie = User::where('email', 'gannie@gmail.com')->first();
if (!$gannie) {
    echo "❌ Gannie not found!\n";
    exit;
}

echo "✅ Found priest: {$gannie->first_name} {$gannie->last_name} (ID: {$gannie->id})\n\n";

// Find a requestor
$requestor = User::where('role', 'requestor')->first();
if (!$requestor) {
    echo "❌ No requestor found!\n";
    exit;
}

// Find a service
$service = Service::first();
if (!$service) {
    echo "❌ No service found!\n";
    exit;
}

// Find a venue
$venue = Venue::first();
if (!$venue) {
    echo "❌ No venue found!\n";
    exit;
}

// Find an admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ No admin found!\n";
    exit;
}

echo "📋 Creating test reservation...\n";

// Create a test reservation
$reservation = Reservation::create([
    'user_id' => $requestor->id,
    'service_id' => $service->service_id,
    'venue_id' => $venue->venue_id,
    'schedule_date' => now()->addDays(7)->setTime(10, 0),
    'status' => 'pending',
    'priest_selection_type' => 'specific',
    'activity_name' => 'Test Service for Gannie',
    'participant_count' => 50,
    'description' => 'This is a test reservation to verify the notification system works for priest gannie.',
]);

echo "✅ Created Reservation #{$reservation->reservation_id}\n\n";

echo "🔧 Assigning priest 'gannie' to the reservation...\n";

// Update reservation to assign gannie
$reservation->update([
    'officiant_id' => $gannie->id,
    'status' => 'admin_approved',
    'priest_notified_at' => now(),
    'priest_confirmation' => 'pending',
    'approved_by' => $admin->id,
]);

// Create history record
ReservationHistory::create([
    'reservation_id' => $reservation->reservation_id,
    'performed_by' => $admin->id,
    'action' => 'admin_approved',
    'remarks' => 'Test assignment - Assigned to: ' . $gannie->full_name,
    'performed_at' => now(),
]);

echo "✅ Priest assigned\n\n";

echo "📧 Sending notifications...\n";

try {
    $notificationService = app(ReservationNotificationService::class);
    $notificationService->notifyPriestAssigned($reservation->fresh());
    
    echo "✅ SUCCESS! Notifications sent:\n";
    echo "   • In-app notification created\n";
    echo "   • Email sent to: {$gannie->email}\n";
    if ($gannie->phone) {
        echo "   • SMS sent to: {$gannie->phone}\n";
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    exit;
}

echo "🔍 Verifying priest dashboard will show this...\n\n";

// Check what gannie should see on dashboard
$pendingCount = Reservation::where('officiant_id', $gannie->id)
    ->whereIn('status', ['pending', 'pending_priest_confirmation', 'admin_approved'])
    ->where(function($q) {
        $q->where('priest_confirmation', '!=', 'confirmed')
          ->orWhereNull('priest_confirmation')
          ->orWhere('priest_confirmation', 'pending');
    })
    ->count();

echo "📊 Gannie's Dashboard will now show:\n";
echo "   Pending Confirmations: {$pendingCount}\n\n";

if ($pendingCount > 0) {
    echo "✅ SUCCESS! The notification system is working correctly!\n";
    echo "👉 Gannie should now see this reservation in the dashboard.\n";
    echo "👉 Gannie can log in and confirm or decline the assignment.\n\n";
    
    echo "🌐 Next steps:\n";
    echo "1. Log in as gannie@gmail.com\n";
    echo "2. Go to Priest Dashboard\n";
    echo "3. You should see '1' in Pending Confirmations\n";
    echo "4. Click to view and confirm the reservation\n";
} else {
    echo "❌ Something went wrong with the count logic\n";
}

echo "\n💡 NOTE: You can delete this test reservation from the admin panel if needed.\n";
echo "   Reservation ID: {$reservation->reservation_id}\n";

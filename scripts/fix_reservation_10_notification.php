<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Reservation;
use App\Services\ReservationNotificationService;

echo "🔍 CHECKING RESERVATION #10 NOTIFICATION ISSUE\n";
echo "==============================================\n\n";

$reservation = Reservation::with(['officiant', 'user', 'service'])->find(10);

if (!$reservation) {
    echo "❌ Reservation #10 not found\n";
    exit;
}

echo "📋 Reservation #10 Details:\n";
echo "   Service: {$reservation->service->service_name}\n";
echo "   Requestor: {$reservation->user->first_name} {$reservation->user->last_name}\n";
echo "   Status: {$reservation->status}\n";
echo "   Schedule: {$reservation->schedule_date}\n\n";

if ($reservation->officiant) {
    echo "👤 Assigned Priest:\n";
    echo "   Name: {$reservation->officiant->first_name} {$reservation->officiant->last_name}\n";
    echo "   ID: {$reservation->officiant->id}\n";
    echo "   Email: {$reservation->officiant->email}\n";
    echo "   Role: {$reservation->officiant->role}\n\n";
} else {
    echo "❌ No priest assigned!\n";
    exit;
}

echo "🔔 Notification Status:\n";
echo "   priest_notified_at: " . ($reservation->priest_notified_at ?? 'NULL') . "\n";
echo "   priest_confirmation: " . ($reservation->priest_confirmation ?? 'NULL') . "\n\n";

if (!$reservation->priest_notified_at) {
    echo "❌ PROBLEM: Priest was assigned but NEVER notified!\n\n";
    
    echo "🔧 ATTEMPTING TO FIX: Sending notification now...\n";
    
    try {
        // Update the timestamp first
        $reservation->update([
            'priest_notified_at' => now(),
            'priest_confirmation' => 'pending',
        ]);
        
        // Send notification
        $notificationService = app(ReservationNotificationService::class);
        $notificationService->notifyPriestAssigned($reservation->fresh());
        
        echo "✅ SUCCESS! Notification sent to priest.\n";
        echo "   • In-app notification created\n";
        echo "   • Email sent to: {$reservation->officiant->email}\n";
        if ($reservation->officiant->phone) {
            echo "   • SMS sent to: {$reservation->officiant->phone}\n";
        }
        echo "\n";
        echo "👉 The priest should now see this reservation in their dashboard!\n";
        
    } catch (\Exception $e) {
        echo "❌ ERROR: Failed to send notification\n";
        echo "   Error: {$e->getMessage()}\n";
    }
} else {
    echo "✅ Priest was notified at: {$reservation->priest_notified_at}\n";
}

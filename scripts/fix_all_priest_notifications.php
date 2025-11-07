<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Reservation;
use App\Services\ReservationNotificationService;

echo "🔧 FIXING ALL PRIEST NOTIFICATION ISSUES\n";
echo "==========================================\n\n";

// Find ALL reservations where priest is assigned but not notified
$problematicReservations = Reservation::whereNotNull('officiant_id')
    ->whereNull('priest_notified_at')
    ->whereIn('status', ['pending', 'pending_priest_confirmation', 'admin_approved', 'adviser_approved'])
    ->with(['officiant', 'user', 'service'])
    ->get();

if ($problematicReservations->isEmpty()) {
    echo "✅ No issues found! All assigned priests have been notified.\n";
    exit;
}

echo "❌ Found {$problematicReservations->count()} reservation(s) with notification issues\n\n";

$notificationService = app(ReservationNotificationService::class);
$fixed = 0;
$failed = 0;

foreach ($problematicReservations as $reservation) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📋 Reservation #{$reservation->reservation_id}\n";
    echo "   Service: {$reservation->service->service_name}\n";
    echo "   Assigned to: {$reservation->officiant->first_name} {$reservation->officiant->last_name}\n";
    echo "   Status: {$reservation->status}\n";
    
    try {
        // Update timestamps
        $reservation->update([
            'priest_notified_at' => now(),
            'priest_confirmation' => $reservation->priest_confirmation ?? 'pending',
        ]);
        
        // Send notification
        $notificationService->notifyPriestAssigned($reservation->fresh());
        
        echo "   ✅ FIXED: Notifications sent successfully\n\n";
        $fixed++;
        
    } catch (\Exception $e) {
        echo "   ❌ FAILED: {$e->getMessage()}\n\n";
        $failed++;
    }
}

echo "\n📊 SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Fixed: {$fixed}\n";
if ($failed > 0) {
    echo "❌ Failed: {$failed}\n";
}
echo "\n👉 All priests should now see their pending confirmations!\n";

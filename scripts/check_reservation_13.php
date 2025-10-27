#!/usr/bin/env php
<?php

// Check reservation #13 details
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Reservation;
use App\Models\Notification;

echo "========================================\n";
echo "CHECKING RESERVATION #13\n";
echo "========================================\n\n";

$reservation = Reservation::with(['organization.adviser', 'officiant', 'user'])
    ->where('reservation_id', 13)
    ->first();

if (!$reservation) {
    echo "❌ Reservation #13 not found\n";
    exit(1);
}

echo "📋 Reservation Details:\n";
echo "   ID: {$reservation->reservation_id}\n";
echo "   Service: {$reservation->service->service_name}\n";
echo "   Status: {$reservation->status}\n";
echo "   Schedule: {$reservation->schedule_date}\n";
echo "   org_id: " . ($reservation->org_id ?? 'NULL') . "\n\n";

echo "👤 Requestor:\n";
echo "   Name: {$reservation->user->first_name} {$reservation->user->last_name}\n";
echo "   ID: {$reservation->user->id}\n\n";

if ($reservation->organization) {
    echo "🏢 Organization:\n";
    echo "   Name: {$reservation->organization->org_name}\n";
    echo "   ID: {$reservation->organization->org_id}\n";

    if ($reservation->organization->adviser) {
        $adviser = $reservation->organization->adviser;
        echo "\n👨‍💼 Adviser:\n";
        echo "   Name: {$adviser->first_name} {$adviser->last_name}\n";
        echo "   ID: {$adviser->id}\n";
        echo "   Email: {$adviser->email}\n";

        // Check for notifications
        $adviserNotifications = Notification::where('user_id', $adviser->id)
            ->where('reservation_id', 13)
            ->get();

        echo "\n   📬 Notifications for adviser: " . $adviserNotifications->count() . "\n";
        foreach ($adviserNotifications as $notif) {
            echo "      - [{$notif->sent_at}] {$notif->message}\n";
        }
    } else {
        echo "\n❌ No adviser assigned to organization\n";
    }
} else {
    echo "❌ No organization assigned (org_id is NULL)\n";
}

if ($reservation->officiant) {
    echo "\n⛪ Priest:\n";
    echo "   Name: {$reservation->officiant->first_name} {$reservation->officiant->last_name}\n";
    echo "   ID: {$reservation->officiant->id}\n";
    echo "   Email: {$reservation->officiant->email}\n";

    // Check for notifications
    $priestNotifications = Notification::where('user_id', $reservation->officiant->id)
        ->where('reservation_id', 13)
        ->get();

    echo "\n   📬 Notifications for priest: " . $priestNotifications->count() . "\n";
    foreach ($priestNotifications as $notif) {
        echo "      - [{$notif->sent_at}] {$notif->message}\n";
    }
} else {
    echo "\n❌ No priest assigned\n";
}

echo "\n========================================\n";
echo "ALL NOTIFICATIONS FOR RESERVATION #13\n";
echo "========================================\n";
$allNotifications = Notification::where('reservation_id', 13)->get();
echo "Total: " . $allNotifications->count() . "\n\n";
foreach ($allNotifications as $notif) {
    echo "User ID: {$notif->user_id}, Sent: {$notif->sent_at}\n";
    echo "Message: {$notif->message}\n";
    echo "---\n";
}

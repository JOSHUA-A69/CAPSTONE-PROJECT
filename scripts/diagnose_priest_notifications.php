<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Reservation;
use App\Models\Notification;

echo "🔍 PRIEST NOTIFICATION SYSTEM DIAGNOSTIC\n";
echo "========================================\n\n";

// Get all priests
$priests = User::where('role', 'priest')->get();

if ($priests->isEmpty()) {
    echo "❌ No priests found in the system!\n";
    exit;
}

echo "✅ Found " . $priests->count() . " priest(s) in the system:\n\n";

foreach ($priests as $priest) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "👤 Priest: {$priest->first_name} {$priest->last_name}\n";
    echo "   ID: {$priest->id}\n";
    echo "   Email: {$priest->email}\n";
    echo "   Role: {$priest->role}\n\n";
    
    // Check reservations assigned to this priest
    $allReservations = Reservation::where('officiant_id', $priest->id)->get();
    echo "   📋 Total Reservations Assigned: " . $allReservations->count() . "\n";
    
    if ($allReservations->count() > 0) {
        // Check pending confirmations (what should show on dashboard)
        $pendingConfirmations = Reservation::where('officiant_id', $priest->id)
            ->whereIn('status', ['pending', 'pending_priest_confirmation', 'admin_approved'])
            ->where(function($q) {
                $q->where('priest_confirmation', '!=', 'confirmed')
                  ->orWhereNull('priest_confirmation')
                  ->orWhere('priest_confirmation', 'pending');
            })
            ->get();
        
        echo "   🔔 Pending Confirmations (Dashboard Count): " . $pendingConfirmations->count() . "\n\n";
        
        if ($pendingConfirmations->count() > 0) {
            echo "   📊 Pending Reservations Details:\n";
            foreach ($pendingConfirmations as $res) {
                echo "      ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "      Reservation #{$res->reservation_id}\n";
                echo "      Service: {$res->service->service_name}\n";
                echo "      Status: {$res->status}\n";
                echo "      Priest Confirmation: " . ($res->priest_confirmation ?? 'NULL') . "\n";
                echo "      Schedule: {$res->schedule_date}\n";
                echo "      Priest Notified At: " . ($res->priest_notified_at ? $res->priest_notified_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
                
                // Check if notification was created
                $notification = Notification::where('user_id', $priest->id)
                    ->where('reservation_id', $res->reservation_id)
                    ->where('type', 'Assignment')
                    ->first();
                
                if ($notification) {
                    echo "      ✅ Notification Created: Yes (ID: {$notification->notification_id})\n";
                    echo "         Sent At: {$notification->sent_at->format('Y-m-d H:i:s')}\n";
                    echo "         Read: " . ($notification->read_at ? 'Yes' : 'No') . "\n";
                    echo "         Message: " . substr($notification->message, 0, 60) . "...\n";
                } else {
                    echo "      ❌ Notification Created: NO - THIS IS THE PROBLEM!\n";
                }
                echo "\n";
            }
        } else {
            echo "   ℹ️  All reservations are either confirmed or don't require confirmation\n\n";
        }
        
        // Check in-app notifications for this priest
        $allNotifications = Notification::where('user_id', $priest->id)->get();
        echo "   📬 Total In-App Notifications: " . $allNotifications->count() . "\n";
        
        if ($allNotifications->count() > 0) {
            $unreadCount = $allNotifications->where('read_at', null)->count();
            echo "   📫 Unread Notifications: {$unreadCount}\n";
            
            $assignmentNotifs = $allNotifications->where('type', 'Assignment')->count();
            echo "   📌 Assignment Notifications: {$assignmentNotifs}\n";
        }
    } else {
        echo "   ℹ️  No reservations assigned to this priest\n";
        echo "   💡 This explains why dashboard shows 0 pending confirmations\n";
    }
    
    echo "\n";
}

echo "\n📊 SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Check total pending reservations awaiting priest assignment
$unassignedReservations = Reservation::whereNull('officiant_id')
    ->whereIn('status', ['pending', 'adviser_approved'])
    ->count();
    
echo "⏳ Reservations Awaiting Priest Assignment: {$unassignedReservations}\n";

// Check reservations assigned but priest not notified
$assignedButNotNotified = Reservation::whereNotNull('officiant_id')
    ->whereNull('priest_notified_at')
    ->whereIn('status', ['pending_priest_confirmation', 'admin_approved'])
    ->count();

if ($assignedButNotNotified > 0) {
    echo "⚠️  ISSUE FOUND: {$assignedButNotNotified} reservations have priest assigned but priest_notified_at is NULL!\n";
    echo "   This could mean notifications failed to send.\n";
} else {
    echo "✅ All assigned reservations have priest_notified_at timestamp set\n";
}

echo "\n";

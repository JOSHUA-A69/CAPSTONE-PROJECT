<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

// Mark all unread messages as read
$updatedMessages = \App\Models\Message::query()
    ->whereNull('read_at')
    ->update(['read_at' => Carbon::now()]);

// Mark all notifications as read
$updatedNotifications = DB::table('notifications')
    ->whereNull('read_at')
    ->update(['read_at' => Carbon::now()]);

// Get remaining counts
$remainingUnreadMessages = \App\Models\Message::query()
    ->whereNull('read_at')
    ->count();

$remainingUnreadNotifications = DB::table('notifications')
    ->whereNull('read_at')
    ->count();

echo "✅ Unread messages marked as read: {$updatedMessages}\n";
echo "✅ Unread notifications marked as read: {$updatedNotifications}\n";
echo "📊 Remaining unread messages: {$remainingUnreadMessages}\n";
echo "📊 Remaining unread notifications: {$remainingUnreadNotifications}\n";

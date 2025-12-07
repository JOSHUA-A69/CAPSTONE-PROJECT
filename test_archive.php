<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;

$notification = Notification::first();
echo "Before archive - deleted_at: " . ($notification->deleted_at ?? 'null') . "\n";

$notification->delete();
$notification->refresh();
echo "After archive - deleted_at: " . ($notification->deleted_at ?? 'null') . "\n";

// Check if it appears in archived
$archived = Notification::onlyTrashed()->first();
echo "Archived count: " . Notification::onlyTrashed()->count() . "\n";
echo "First archived notification ID: " . ($archived->notification_id ?? 'none') . "\n";

// Check if it's excluded from index
$active = Notification::whereNull('deleted_at')->count();
echo "Active notifications: " . $active . "\n";

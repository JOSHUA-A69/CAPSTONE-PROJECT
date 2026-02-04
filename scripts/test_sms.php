<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

// 1. Load Config
$apiKey = Config::get('services.semaphore.api_key');
$senderName = Config::get('services.semaphore.sender_name');
$isEnabled = Config::get('services.semaphore.enabled');

echo "\n--- SMS Configuration Status ---\n";
echo "Enabled (SMS_ENABLED): " . ($isEnabled ? "TRUE" : "FALSE") . "\n";
echo "Sender Name (SEMAPHORE_SENDER_NAME): " . ($senderName ?: "None (Default)") . "\n";
echo "API Key (SEMAPHORE_API_KEY): " . (!empty($apiKey) ? "Set (" . substr($apiKey, 0, 4) . "...)" : "Not Set") . "\n\n";

if (!$isEnabled) {
    echo "⚠️  WARNING: SMS is currently disabled in your .env file (SMS_ENABLED=false).\n";
    echo "   The message will probably not be sent unless you enable it.\n\n";
}

if (empty($apiKey)) {
    echo "❌  ERROR: API Key is missing in .env (SEMAPHORE_API_KEY). Aborting.\n";
    exit(1);
}

// 2. Ask for a number to test
echo "Enter a phone number to test (e.g., 09171234567): ";
$handle = fopen("php://stdin", "r");
$number = trim(fgets($handle));

if (empty($number)) {
    echo "❌  No number provided.\n";
    exit(1);
}

// 3. Format Number (Simple version matching the Service)
$cleanNumber = preg_replace('/[^0-9]/', '', $number);
// Convert to 63 format if it starts with 0
if (str_starts_with($cleanNumber, '0')) {
    $cleanNumber = '63' . substr($cleanNumber, 1);
}

echo "Sending test message to: $cleanNumber...\n";

// 4. Send Request
try {
    $payload = [
        'apikey' => $apiKey,
        'number' => $cleanNumber,
        'message' => "Test message from eReligiousServices: " . date('Y-m-d H:i:s'),
        'sendername' => $senderName,
    ];

    $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', $payload);

    if ($response->successful()) {
        echo "✅  SMS Sent Successfully!\n";
        echo "Response: " . $response->body() . "\n";
    } else {
        echo "❌  Failed to send SMS.\n";
        echo "Status Code: " . $response->status() . "\n";
        echo "Response Body: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "❌  Exception: " . $e->getMessage() . "\n";
}

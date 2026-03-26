<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

// 1. Determine Provider
$provider = env('SMS_PROVIDER', 'semaphore'); // Default to semaphore if not set
$isEnabled = env('SMS_ENABLED', false);

echo "\n--- SMS Configuration Status ---\n";
echo "Enabled (SMS_ENABLED): " . ($isEnabled ? "TRUE" : "FALSE") . "\n";
echo "Active Provider (SMS_PROVIDER): " . strtoupper($provider) . "\n\n";

if (!$isEnabled) {
    echo "⚠️  WARNING: SMS is currently disabled in your .env file.\n";
    exit(1);
}

// 2. Ask for a number to test
echo "Enter a phone number to test (e.g., +639171234567 or 09171234567): ";
$handle = fopen("php://stdin", "r");
$number = trim(fgets($handle));

if (empty($number)) {
    echo "❌  No number provided.\n";
    exit(1);
}

// 3. Format Number
$cleanNumber = preg_replace('/[^0-9]/', '', $number);
// Basic formatting for PH
if (str_starts_with($cleanNumber, '0')) {
    $cleanNumber = '63' . substr($cleanNumber, 1);
}
// Ensure international format for Twilio
$internationalNumber = '+' . $cleanNumber;

echo "Sending test message to: $internationalNumber ($provider)...\n";

try {
    if ($provider === 'twilio') {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_FROM_NUMBER');
        
        if (empty($sid) || empty($token) || empty($from)) {
            echo "❌  Missing Twilio credentials in .env\n";
            exit(1);
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $response = Http::withBasicAuth($sid, $token)->asForm()->post($url, [
            'To' => $internationalNumber,
            'From' => $from,
            'Body' => "Test message from eReligiousServices (Twilio): " . date('Y-m-d H:i:s'),
        ]);

        if ($response->successful()) {
            echo "✅  Twilio SMS Sent Successfully!\n";
            echo "Response: " . $response->body() . "\n";
        } else {
            echo "❌  Twilio Error: " . $response->body() . "\n";
        }

    } elseif ($provider === 'semaphore') {
        $apiKey = env('SEMAPHORE_API_KEY');
        if (empty($apiKey)) {
            echo "❌  Missing Semaphore API Key in .env\n";
            exit(1);
        }

        $senderName = env('SEMAPHORE_SENDER_NAME', 'CREaM-HNU');

        $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => $apiKey,
            'number' => $cleanNumber,
            'message' => "Test message from eReligiousServices: " . date('Y-m-d H:i:s'),
            'sendername' => $senderName,
        ]);

        if ($response->successful()) {
            echo "✅  Semaphore SMS Sent Successfully!\n";
        } else {
            echo "❌  Semaphore Error: " . $response->body() . "\n";
        }
    } else {
        echo "❌  Unknown SMS Provider: $provider\n";
    }

} catch (\Exception $e) {
    echo "❌  Exception: " . $e->getMessage() . "\n";
}

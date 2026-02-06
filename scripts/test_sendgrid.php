<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "Testing SendGrid email configuration...\n";
echo "Mail Driver: " . config('mail.default') . "\n";
echo "Mail Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Mail Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Mail From: " . config('mail.from.address') . "\n\n";

try {
    Mail::raw('This is a test email from SendGrid via Laravel!', function($message) {
        $message->to('antiola.james_lloyd@hnu.edu.ph')
                ->subject('SendGrid Test - eReligiousServices');
    });
    echo "✅ Email sent successfully! Check your inbox.\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

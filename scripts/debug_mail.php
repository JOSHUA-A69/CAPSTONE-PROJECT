<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "=== Current Mail Configuration ===\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n\n";

echo "=== Laravel Config Values ===\n";
echo "mail.default: " . Config::get('mail.default') . "\n";
echo "mail.mailers.smtp.host: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "mail.mailers.smtp.port: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "mail.from.address: " . Config::get('mail.from.address') . "\n\n";

// Test email
echo "=== Sending Test Email ===\n";
try {
    Mail::raw('Test verification email from SendGrid - this should go to your REAL email!', function($message) {
        $message->to('antiola.james_lloyd@hnu.edu.ph')
                ->subject('SendGrid Email Verification Test');
    });
    echo "✅ Email sent successfully via SendGrid! Check your REAL email inbox.\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
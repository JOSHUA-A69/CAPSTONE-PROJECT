<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

// Use the configured from address as the recipient for the test
$toEmail = Config::get('mail.from.address');

echo "--------------------------------------------------\n";
echo "Testing Email Configuration\n";
echo "Mailer: " . Config::get('mail.default') . "\n";
echo "Host: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "Port: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "From: " . $toEmail . "\n";
echo "--------------------------------------------------\n";
echo "Attempting to send test email to: $toEmail\n";

try {
    Mail::raw('This is a test email from your Capstone Project (eReligiousServices) via SendGrid! If you see this, your SMTP configuration is correct.', function ($message) use ($toEmail) {
        $message->to($toEmail)
                ->subject('✅ SendGrid Configuration Success Test');
    });
    echo "✅ SUCCESS: PHP executed the send function without crashing.\n";
    echo "👉 ACTION REQUIRED: Check your inbox (and spam folder) for the email.\n";
} catch (\Exception $e) {
    echo "❌ FAILED: Could not send email.\n";
    echo "Error Message: " . $e->getMessage() . "\n";
}

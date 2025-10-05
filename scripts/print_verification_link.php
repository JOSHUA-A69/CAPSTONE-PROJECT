<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? null;
if (! $email) {
    echo "Usage: php print_verification_link.php user@example.com\n";
    exit(1);
}

$user = App\Models\User::where('email', $email)->first();
if (! $user) {
    echo "User not found: $email\n";
    exit(1);
}

use Illuminate\Support\Facades\URL;

$minutes = 60;
$url = URL::temporarySignedRoute('verification.verify', now()->addMinutes($minutes), [
    'id' => $user->getKey(),
    'hash' => sha1($user->getEmailForVerification()),
]);

echo $url . PHP_EOL;

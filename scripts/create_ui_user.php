<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'ui_test_'.time().'@example.com';
$user = App\Models\User::create([
    'first_name' => 'UI',
    'last_name' => 'Tester',
    'email' => $email,
    'password' => bcrypt('password'),
    'role' => 'requestor',
    'status' => 'pending',
]);

event(new Illuminate\Auth\Events\Registered($user));

try {
    $user->sendEmailVerificationNotification();
    echo "created {$email}\n";
} catch (\Throwable $e) {
    echo "failed to send verification: " . $e->getMessage() . "\n";
}

<?php
// Minimal bootstrap to run inside project root and use the app container
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
// Bootstrapping the application
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Auth\Events\Registered;

$user = User::create([
    'first_name' => 'T',
    'last_name' => 'User',
    'email' => 'verifytest@example.com',
    'password' => bcrypt('password'),
    'role' => 'requestor',
    'status' => 'pending',
]);

event(new Registered($user));

echo "Created user and fired Registered event.\n";

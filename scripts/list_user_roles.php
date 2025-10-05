<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('user_roles')->get();
foreach ($rows as $r) {
    echo $r->user_role_id . '|' . $r->role_name . PHP_EOL;
}

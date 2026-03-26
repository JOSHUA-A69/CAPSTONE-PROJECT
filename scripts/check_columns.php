<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Users Table Columns:\n";
print_r(Schema::getColumnListing('users'));

echo "\nReservations Table Columns:\n";
print_r(Schema::getColumnListing('reservations'));

echo "\nOrganization Booking Requests Table Columns:\n";
print_r(Schema::getColumnListing('organization_booking_requests'));

echo "\nServices Table Columns:\n";
print_r(Schema::getColumnListing('services'));

<?php

$res = new stdClass();
$res->venue = null;

try {
    echo "Testing strict access with coalesce: ";
    // This represents $reservation->venue->name ?? 'N/A' where venue is null
    $val = $res->venue->name ?? 'N/A'; 
    echo "Result: " . $val . "\n";
} catch (\Throwable $e) {
    echo "CRASH: " . $e->getMessage() . "\n";
}

try {
    echo "Testing safe access with coalesce: ";
    // This represents $reservation->venue?->name ?? 'N/A'
    $val = $res->venue?->name ?? 'N/A';
    echo "Result: " . $val . "\n";
} catch (\Throwable $e) {
    echo "CRASH: " . $e->getMessage() . "\n";
}

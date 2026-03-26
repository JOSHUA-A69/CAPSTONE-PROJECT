<?php

class MockReservation {
    public $venue = null;
}

$reservation = new MockReservation();

// Case 1: Direct access in catch block checks crash
try {
    $val = $reservation->venue->name;
} catch (\Throwable $e) {
    echo "Direct access throws: " . $e->getMessage() . "\n";
}

// Case 2: Coalesce access
try {
    $val = $reservation->venue->name ?? 'N/A';
    echo "Coalesce access result: " . $val . "\n";
} catch (\Throwable $e) {
    echo "Coalesce access throws: " . $e->getMessage() . "\n";
}

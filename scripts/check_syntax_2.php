<?php

$res = new stdClass();
// $res->venue is undefined

try {
    echo "Testing strict access on undefined property with coalesce: ";
    $val = $res->venue->name ?? 'N/A'; 
    echo "Result: " . $val . "\n";
} catch (\Throwable $e) {
    echo "CRASH: " . $e->getMessage() . "\n";
}

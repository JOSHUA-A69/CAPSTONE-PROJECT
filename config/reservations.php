<?php

return [
    // Minutes around the scheduled time considered as a conflict window
    // Example: 120 means any reservation within +/- 120 minutes is a conflict
    'conflict_minutes' => (int) env('RESERVATION_CONFLICT_MINUTES', 120),
];

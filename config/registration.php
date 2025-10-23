<?php

return [
    // Allow role selection on the public registration form. Set to true for dev/testing only.
    'allow_role_selection' => env('ALLOW_ROLE_SELECTION', true),

    // Roles that require an elevated registration code when self-selected.
    'elevated_roles' => ['admin', 'staff', 'adviser', 'priest'],

    // Comma-separated list of one-time codes that permit elevated-role self-registration
    // Example: ELEVATED_REGISTRATION_CODES=code1,code2
    'elevated_codes' => array_filter(array_map('trim', explode(',', env('ELEVATED_REGISTRATION_CODES', '')))),
];

<?php

/**
 * Kenya payroll statutory settings.
 * Verify rates with your accountant / KRA before production use.
 */
return [
    'currency' => 'KES',

    'personal_relief' => 2400.00,

    'paye_bands' => [
        ['min' => 0, 'max' => 24000, 'rate' => 0.10],
        ['min' => 24001, 'max' => 32333, 'rate' => 0.25],
        ['min' => 32334, 'max' => 500000, 'rate' => 0.30],
        ['min' => 500001, 'max' => 800000, 'rate' => 0.325],
        ['min' => 800001, 'max' => null, 'rate' => 0.35],
    ],

    'nssf' => [
        'tier1_limit' => 7000,
        'tier2_limit' => 36000,
        'rate' => 0.06,
    ],

    'shif_rate' => 0.0275,

    'housing_levy' => [
        'employee_rate' => 0.015,
        'employer_rate' => 0.015,
    ],
];

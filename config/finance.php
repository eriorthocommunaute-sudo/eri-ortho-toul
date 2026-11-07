<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Monthly Contribution Amount
    |--------------------------------------------------------------------------
    |
    | The default amount expected from each active member per month.
    | This can be overridden per contribution period in the Contributions module.
    |
    */
    'default_monthly_contribution' => env('DEFAULT_MONTHLY_CONTRIBUTION', 10.00),

    /*
    |--------------------------------------------------------------------------
    | Contribution Status Thresholds
    |--------------------------------------------------------------------------
    */
    'contribution_status' => [
        'paid' => 1.0,      // 100% or more of expected amount
        'partial' => 0.01,  // At least 1 cent but less than 100%
        'unpaid' => 0.0,    // 0 paid
    ],
];
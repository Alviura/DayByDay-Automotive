<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default sales tax rate (decimal, e.g. 0.16 = 16%)
    |--------------------------------------------------------------------------
    */

    'tax_rate' => (float) env('SALES_TAX_RATE', 0),

    /*
    |--------------------------------------------------------------------------
    | Line discount % above which approval is required (future M11 hook)
    |--------------------------------------------------------------------------
    */

    'discount_approval_threshold' => (float) env('SALES_DISCOUNT_THRESHOLD', 20),

];

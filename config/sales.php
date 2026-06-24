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
    | VAT remittance (Phase F0 placeholder — full workflow in finance module)
    |--------------------------------------------------------------------------
    */

    'vat' => [
        'enabled' => (bool) env('SALES_VAT_ENABLED', true),
        'remittance_due_day' => (int) env('SALES_VAT_DUE_DAY', 20),
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default GL account codes (ChartOfAccountSeeder must create these)
    |--------------------------------------------------------------------------
    */

    'accounts' => [
        'bank' => '1100',
        'ar_fleet' => '1200',
        'inventory' => '1300',
        'grni' => '1400',
        'ap_suppliers' => '2100',
        'vat_payable' => '2200',
        'paye_payable' => '2310',
        'nssf_employee_payable' => '2320',
        'nssf_employer_payable' => '2321',
        'shif_payable' => '2330',
        'housing_levy_payable' => '2340',
        'wages_payable' => '2400',
        'sales_revenue' => '4000',
        'sales_returns' => '4900',
        'cogs' => '5000',
        'salaries_wages' => '6100',
        'employer_statutory' => '6200',
        'inventory_shrinkage' => '6300',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cash account code pattern: {prefix}-{shop_code}-{method}
    |--------------------------------------------------------------------------
    */

    'cash_account_prefix' => '1110',

    /*
    |--------------------------------------------------------------------------
    | Automated GL posting (Phase F2)
    |--------------------------------------------------------------------------
    */

    'auto_posting' => (bool) env('FINANCE_AUTO_POSTING', true),

    /*
    |--------------------------------------------------------------------------
    | P2 posting — inter-location transfers (no P&L; inventory reclass only)
    |--------------------------------------------------------------------------
    */

    'post_transfers' => (bool) env('FINANCE_POST_TRANSFERS', true),

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Approval module metadata
    |--------------------------------------------------------------------------
    |
    | Keys match ApprovableDocument::approvalModuleKey() on each document type.
    |
    */

    'modules' => [
        'demonstration' => [
            'label' => 'Demo Request',
            'icon' => 'fa-flask',
            'pipeline' => false,
        ],
        'quotation-series' => [
            'label' => 'Quotation Series',
            'icon' => 'fa-file-invoice-dollar',
            'pipeline' => true,
            'legacy' => true,
        ],
        'transfer' => [
            'label' => 'Stock Transfer',
            'icon' => 'fa-right-left',
            'pipeline' => true,
        ],
        'adjustment' => [
            'label' => 'Stock Adjustment',
            'icon' => 'fa-boxes-stacked',
            'pipeline' => true,
        ],
        'return' => [
            'label' => 'Return',
            'icon' => 'fa-rotate-left',
            'pipeline' => true,
        ],
        'procurement' => [
            'label' => 'Quotation Series (legacy)',
            'icon' => 'fa-file-invoice-dollar',
            'pipeline' => false,
            'legacy' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default approver resolution
    |--------------------------------------------------------------------------
    |
    | When a document does not specify an approver, the first active user with
    | the Administrator role is assigned.
    |
    */

    'default_approver_role' => 'Administrator',

    /*
    |--------------------------------------------------------------------------
    | Module → model class map (for inbox filtering)
    |--------------------------------------------------------------------------
    */

    'module_models' => [
        'demonstration' => \App\Models\ApprovalDemonstration::class,
        'adjustment' => \App\Models\StockAdjustment::class,
        'quotation-series' => \App\Models\QuotationSeries::class,
        'procurement' => \App\Models\QuotationSeries::class,
        'transfer' => \App\Models\TransferRequest::class,
        'return' => \App\Models\ReturnRecord::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reference columns for inbox search
    |--------------------------------------------------------------------------
    */

    'search_columns' => [
        \App\Models\ApprovalDemonstration::class => ['reference', 'title'],
        \App\Models\StockAdjustment::class => ['adjustment_number'],
        \App\Models\QuotationSeries::class => ['series_number', 'title'],
        \App\Models\TransferRequest::class => ['request_number'],
        \App\Models\ReturnRecord::class => ['return_number'],
    ],

];

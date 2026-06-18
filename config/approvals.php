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
        ],
        'procurement' => [
            'label' => 'Procurement',
            'icon' => 'fa-file-invoice-dollar',
        ],
        'transfer' => [
            'label' => 'Transfer Request',
            'icon' => 'fa-right-left',
        ],
        'adjustment' => [
            'label' => 'Stock Adjustment',
            'icon' => 'fa-boxes-stacked',
        ],
        'return' => [
            'label' => 'Return',
            'icon' => 'fa-rotate-left',
        ],
        'discount' => [
            'label' => 'Sale Discount',
            'icon' => 'fa-percent',
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
    ],

];

<?php

/**
 * Central permission registry — single source of truth for RoleSeeder and the Roles UI.
 *
 * Keys under groups.*.permissions are permission names; values are human labels.
 * role_defaults seeds the four core roles on fresh install / db:seed.
 */
return [

    'groups' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'permissions' => [
                'dashboard.view' => 'View dashboard',
            ],
        ],
        'users' => [
            'label' => 'Users',
            'permissions' => [
                'users.view' => 'View users',
                'users.create' => 'Create users',
                'users.edit' => 'Edit users',
                'users.delete' => 'Delete users',
            ],
        ],
        'roles' => [
            'label' => 'Roles & permissions',
            'permissions' => [
                'roles.view' => 'View roles',
                'roles.manage' => 'Create and edit roles',
            ],
        ],
        'warehouses' => [
            'label' => 'Warehouses',
            'permissions' => [
                'warehouses.view' => 'View warehouses',
                'warehouses.manage' => 'Manage warehouses',
            ],
        ],
        'shops' => [
            'label' => 'Shops',
            'permissions' => [
                'shops.view' => 'View shops',
                'shops.manage' => 'Manage shops',
            ],
        ],
        'suppliers' => [
            'label' => 'Suppliers',
            'permissions' => [
                'suppliers.view' => 'View suppliers',
                'suppliers.manage' => 'Manage suppliers',
            ],
        ],
        'master-data' => [
            'label' => 'Master data & catalog',
            'permissions' => [
                'master-data.view' => 'View catalog lookups',
                'master-data.manage' => 'Manage catalog lookups',
            ],
        ],
        'products' => [
            'label' => 'Products',
            'permissions' => [
                'products.view' => 'View products',
                'products.create' => 'Create products',
                'products.edit' => 'Edit products',
                'products.archive' => 'Archive products',
            ],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'permissions' => [
                'inventory.view' => 'View stock levels',
                'inventory.adjust' => 'Create stock adjustments',
                'inventory.adjust.approve' => 'Assigned approver for adjustments',
            ],
        ],
        'procurement' => [
            'label' => 'Procurement',
            'permissions' => [
                'procurement.view' => 'View quotations, POs, and GRNs',
                'procurement.manage' => 'Manage procurement workflow',
            ],
        ],
        'supplier_payments' => [
            'label' => 'Supplier payments',
            'permissions' => [
                'supplier_payments.view' => 'View supplier payments',
                'supplier_payments.manage' => 'Record and void payments',
            ],
        ],
        'finance' => [
            'label' => 'Finance & GL',
            'permissions' => [
                'finance.view' => 'View finance modules',
                'finance.manage' => 'Manage COA, periods, tax, and bank recon',
                'finance.journal' => 'Create manual journal entries',
                'finance.approve' => 'Assigned approver for journal entries',
            ],
        ],
        'transfer_requests' => [
            'label' => 'Transfer requests',
            'permissions' => [
                'transfer_requests.view' => 'View transfer requests',
                'transfer_requests.create' => 'Submit transfer requests',
                'transfer_requests.review' => 'Accept or reject requests',
            ],
        ],
        'transfers' => [
            'label' => 'Stock transfers',
            'permissions' => [
                'transfers.view' => 'View stock transfers',
                'transfers.create' => 'Create and submit transfers',
                'transfers.approve' => 'Assigned approver for transfers',
                'transfers.dispatch' => 'Dispatch transfers',
                'transfers.receive' => 'Receive transfers',
            ],
        ],
        'sales' => [
            'label' => 'Sales',
            'permissions' => [
                'sales.view' => 'View sales history',
                'sales.create' => 'Complete sales and checkout',
                'sales.hold' => 'Hold and resume orders',
                'sales.reverse' => 'Reverse completed sales',
            ],
        ],
        'customer_accounts' => [
            'label' => 'Customer accounts',
            'permissions' => [
                'customer_accounts.view' => 'View customer accounts',
                'customer_accounts.manage' => 'Manage customer accounts',
            ],
        ],
        'customer_invoices' => [
            'label' => 'Customer invoices',
            'permissions' => [
                'customer_invoices.view' => 'View invoices',
                'customer_invoices.manage' => 'Create invoices and record payments',
            ],
        ],
        'returns' => [
            'label' => 'Returns',
            'permissions' => [
                'returns.view' => 'View returns',
                'returns.create' => 'Create and submit returns',
                'returns.approve' => 'Assigned approver for returns',
            ],
        ],
        'reports' => [
            'label' => 'Reports',
            'permissions' => [
                'reports.view' => 'View reports',
                'reports.export' => 'Export reports',
            ],
        ],
        'audit' => [
            'label' => 'Audit log',
            'permissions' => [
                'audit.view' => 'View audit log',
            ],
        ],
        'approvals' => [
            'label' => 'Approvals',
            'permissions' => [
                'approvals.act' => 'Open inbox and approve or reject documents',
            ],
        ],
        'notifications' => [
            'label' => 'Notifications',
            'permissions' => [
                'notifications.view' => 'View notification center',
            ],
        ],
        'employees' => [
            'label' => 'Employees',
            'permissions' => [
                'employees.view' => 'View employees',
                'employees.manage' => 'Manage employees',
            ],
        ],
        'payroll' => [
            'label' => 'Payroll',
            'permissions' => [
                'payroll.view' => 'View payroll periods',
                'payroll.run' => 'Generate payroll',
                'payroll.lock' => 'Lock periods and mark paid',
                'payroll.export' => 'Export payroll',
            ],
        ],
        'payslips' => [
            'label' => 'Payslips',
            'permissions' => [
                'payslips.view_own' => 'View own payslips',
            ],
        ],
    ],

    'role_defaults' => [
        'Administrator' => '*',

        'Shop Manager' => [
            'dashboard.view',
            'notifications.view',
            'shops.view',
            'products.view',
            'inventory.view',
            'sales.view', 'sales.create', 'sales.hold', 'sales.reverse',
            'customer_accounts.view', 'customer_accounts.manage',
            'customer_invoices.view', 'customer_invoices.manage',
            'returns.view', 'returns.create',
            'transfer_requests.view', 'transfer_requests.create', 'transfer_requests.review',
            'transfers.view', 'transfers.create', 'transfers.dispatch', 'transfers.receive',
            'reports.view',
        ],

        'Shop Attendant' => [
            'dashboard.view',
            'notifications.view',
            'products.view',
            'sales.view', 'sales.create', 'sales.hold',
            'payslips.view_own',
        ],

        'Warehouse Manager' => [
            'dashboard.view',
            'notifications.view',
            'warehouses.view',
            'products.view',
            'inventory.view',
            'inventory.adjust',
            'transfer_requests.view', 'transfer_requests.review',
            'transfers.view', 'transfers.create', 'transfers.dispatch', 'transfers.receive',
            'procurement.view', 'procurement.manage',
            'returns.view', 'returns.create',
            'reports.view',
        ],
    ],

];

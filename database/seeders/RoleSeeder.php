<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Permissions grouped by module. Administrators receive all of them;
     * Shop Managers receive a shop-scoped operational subset.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',
            // Users & roles
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.manage',
            // Locations
            'warehouses.view', 'warehouses.manage',
            'shops.view', 'shops.manage',
            // Suppliers
            'suppliers.view', 'suppliers.manage',
            // Master data / lookups (vehicle makes/models, categories, units, product names)
            'master-data.view', 'master-data.manage',
            // Products
            'products.view', 'products.create', 'products.edit', 'products.archive',
            // Inventory
            'inventory.view', 'inventory.adjust', 'inventory.adjust.approve',
            // Procurement
            'procurement.view', 'procurement.manage', 'procurement.approve',
            // Distribution / transfers
            'transfers.view', 'transfers.request', 'transfers.approve', 'transfers.dispatch', 'transfers.receive',
            // Sales
            'sales.view', 'sales.create', 'sales.hold', 'sales.reverse',
            'customer_accounts.view', 'customer_accounts.manage',
            'customer_invoices.view', 'customer_invoices.manage',
            // Returns
            'returns.view', 'returns.create', 'returns.approve',
            // Reporting
            'reports.view', 'reports.export',
            // Audit & approvals
            'audit.view',
            'approvals.act',
            // HR & Payroll
            'employees.view', 'employees.manage',
            'payroll.view', 'payroll.run', 'payroll.lock',
            'payslips.view_own',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $shopManager = Role::firstOrCreate(['name' => 'Shop Manager', 'guard_name' => 'web']);
        $shopManager->syncPermissions([
            'dashboard.view',
            'shops.view',
            'products.view',
            'inventory.view',
            'sales.view', 'sales.create', 'sales.hold', 'sales.reverse',
            'customer_accounts.view', 'customer_accounts.manage',
            'customer_invoices.view', 'customer_invoices.manage',
            'returns.view', 'returns.create',
            'transfers.view', 'transfers.request', 'transfers.receive',
            'reports.view',
            'approvals.act',
        ]);

        $shopAttendant = Role::firstOrCreate(['name' => 'Shop Attendant', 'guard_name' => 'web']);
        $shopAttendant->syncPermissions([
            'dashboard.view',
            'products.view',
            'sales.view', 'sales.create', 'sales.hold',
            'payslips.view_own',
        ]);
    }
}

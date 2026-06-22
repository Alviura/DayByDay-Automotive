<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            WarehouseSeeder::class,
            ShopSeeder::class,
            SupplierSeeder::class,
            VehicleMakeSeeder::class,
            CategorySeeder::class,
            ProductNameSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            ApprovalSeeder::class,
            InventorySeeder::class,
            ProcurementSeeder::class,
            TransferSeeder::class,
            SaleSeeder::class,
            CustomerAccountSeeder::class,
            ReturnSeeder::class,
            AuditLogSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}

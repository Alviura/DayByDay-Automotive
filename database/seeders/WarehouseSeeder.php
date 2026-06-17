<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH-MAIN',
                'address' => 'Industrial Area, Plot 12',
                'phone' => '+254 700 000 001',
                'is_active' => true,
            ],
            [
                'name' => 'East Side Storage',
                'code' => 'WH-EAST',
                'address' => 'Eastlands, Block C',
                'phone' => '+254 700 000 002',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $data) {
            Warehouse::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}

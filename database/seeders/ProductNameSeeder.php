<?php

namespace Database\Seeders;

use App\Models\ProductName;
use Illuminate\Database\Seeder;

class ProductNameSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Oil Filter',
            'Air Filter',
            'Brake Pad',
            'Spark Plug',
            'Shock Absorber',
            'Timing Belt',
            'Water Pump',
            'Alternator',
            'Starter Motor',
            'Radiator',
        ];

        foreach ($names as $name) {
            ProductName::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}

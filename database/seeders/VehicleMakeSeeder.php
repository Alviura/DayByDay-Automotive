<?php

namespace Database\Seeders;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class VehicleMakeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Toyota' => ['Corolla', 'Hilux', 'RAV4', 'Land Cruiser'],
            'Nissan' => ['X-Trail', 'Navara', 'Note'],
            'Mazda' => ['CX-5', 'Demio', 'BT-50'],
        ];

        foreach ($data as $makeName => $models) {
            $make = VehicleMake::updateOrCreate(
                ['name' => $makeName],
                ['is_active' => true]
            );

            foreach ($models as $modelName) {
                VehicleModel::updateOrCreate(
                    ['vehicle_make_id' => $make->id, 'name' => $modelName],
                    ['is_active' => true]
                );
            }
        }
    }
}

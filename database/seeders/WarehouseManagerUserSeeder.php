<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WarehouseManagerUserSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::query()->active()->orderBy('id')->first();

        if (! $warehouse) {
            return;
        }

        $manager = User::firstOrCreate(
            ['email' => 'warehouse@daybyday.test'],
            [
                'name' => 'Warehouse Manager',
                'password' => Hash::make('password'),
                'warehouse_id' => $warehouse->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $manager->update(['warehouse_id' => $warehouse->id]);
        $manager->syncRoles(['Warehouse Manager']);
    }
}

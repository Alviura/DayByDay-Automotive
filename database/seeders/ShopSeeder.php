<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'name' => 'Downtown Auto Parts',
                'code' => 'SH-DTOWN',
                'address' => 'Kimathi Street, Nairobi CBD',
                'phone' => '+254 700 100 001',
                'is_active' => true,
            ],
            [
                'name' => 'Westlands Branch',
                'code' => 'SH-WEST',
                'address' => 'Ring Road, Westlands',
                'phone' => '+254 700 100 002',
                'is_active' => true,
            ],
        ];

        foreach ($shops as $data) {
            Shop::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}

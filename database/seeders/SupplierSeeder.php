<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'AutoParts Global Ltd',
                'code' => 'SUP-AGL',
                'contact_person' => 'James Mwangi',
                'phone' => '+254 720 100 101',
                'email' => 'orders@autopartsglobal.co.ke',
                'country' => 'Kenya',
                'currency' => 'KES',
                'lead_time_days' => 7,
                'rating' => 4.50,
                'address' => 'Mombasa Road, Nairobi',
                'is_active' => true,
            ],
            [
                'name' => 'Nippon Motor Supplies',
                'code' => 'SUP-NMS',
                'contact_person' => 'Yuki Tanaka',
                'phone' => '+81 3 1234 5678',
                'email' => 'export@nipponmotor.jp',
                'country' => 'Japan',
                'currency' => 'USD',
                'lead_time_days' => 21,
                'rating' => 4.80,
                'address' => 'Tokyo, Japan',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}

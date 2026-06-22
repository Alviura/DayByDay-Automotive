<?php

namespace Database\Seeders;

use App\Models\CustomerAccount;
use Illuminate\Database\Seeder;

class CustomerAccountSeeder extends Seeder
{
    public function run(): void
    {
        CustomerAccount::firstOrCreate(
            ['name' => "Jane's PSV Fleet"],
            [
                'contact_name' => 'Jane Wanjiku',
                'phone' => '+254 712 345 678',
                'email' => 'jane.psv@example.com',
                'billing_terms' => 'monthly',
                'credit_limit' => 500000,
                'notes' => 'Family PSV fleet — spare parts billed monthly.',
                'is_active' => true,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $shop = Shop::first();
        if (! $shop) {
            return;
        }

        $driver = Employee::firstOrCreate(
            ['employee_number' => 'EMP-'.now()->format('Y').'-0001'],
            [
                'first_name' => 'James',
                'last_name' => 'Kamau',
                'phone' => '+254712000001',
                'job_title' => 'Delivery Driver',
                'employment_type' => 'permanent',
                'hire_date' => now()->subMonths(6),
                'station_type' => 'field',
                'is_active' => true,
            ]
        );

        if (! $driver->currentSalary) {
            EmployeeSalary::create([
                'employee_id' => $driver->id,
                'basic_salary' => 35000,
                'transport_allowance' => 8000,
                'payment_method' => 'mpesa',
                'effective_from' => now()->subMonths(6)->toDateString(),
            ]);
        }

        $attendantUser = User::firstOrCreate(
            ['email' => 'attendant@daybyday.test'],
            [
                'name' => 'Mary Wanjiku',
                'phone' => '+254712000002',
                'password' => Hash::make('password'),
                'shop_id' => $shop->id,
                'is_active' => true,
            ]
        );
        $attendantUser->syncRoles(['Shop Attendant']);

        $attendant = Employee::firstOrCreate(
            ['user_id' => $attendantUser->id],
            [
                'employee_number' => Employee::generateNumber(),
                'first_name' => 'Mary',
                'last_name' => 'Wanjiku',
                'email' => $attendantUser->email,
                'phone' => '+254712000002',
                'job_title' => 'Shop Attendant',
                'employment_type' => 'permanent',
                'hire_date' => now()->subYear(),
                'station_type' => 'shop',
                'shop_id' => $shop->id,
                'is_active' => true,
            ]
        );

        if (! $attendant->currentSalary) {
            EmployeeSalary::create([
                'employee_id' => $attendant->id,
                'basic_salary' => 28000,
                'housing_allowance' => 5000,
                'payment_method' => 'bank',
                'bank_name' => 'Equity Bank',
                'account_number' => '0123456789',
                'effective_from' => now()->subYear()->toDateString(),
            ]);
        }
    }
}

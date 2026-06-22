<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function create(array $employeeData, array $salaryData, ?array $userData = null): Employee
    {
        return DB::transaction(function () use ($employeeData, $salaryData, $userData) {
            if ($userData) {
                $user = User::create([
                    'name' => trim($employeeData['first_name'].' '.($employeeData['last_name'] ?? '')),
                    'email' => $userData['email'],
                    'phone' => $employeeData['phone'] ?? null,
                    'password' => Hash::make($userData['password']),
                    'shop_id' => $employeeData['shop_id'] ?? null,
                    'warehouse_id' => $employeeData['warehouse_id'] ?? null,
                    'is_active' => $employeeData['is_active'] ?? true,
                ]);
                $user->syncRoles([$userData['role']]);
                $employeeData['user_id'] = $user->id;
                $employeeData['email'] = $userData['email'];
            }

            $employeeData['employee_number'] = Employee::generateNumber();
            $employee = Employee::create($employeeData);

            $this->upsertSalary($employee, $salaryData);

            return $employee->load(['currentSalary', 'shop', 'warehouse', 'user']);
        });
    }

    public function update(Employee $employee, array $employeeData, array $salaryData, ?array $userData = null): Employee
    {
        return DB::transaction(function () use ($employee, $employeeData, $salaryData, $userData) {
            if ($userData && ! $employee->user_id) {
                $user = User::create([
                    'name' => trim($employeeData['first_name'].' '.($employeeData['last_name'] ?? '')),
                    'email' => $userData['email'],
                    'phone' => $employeeData['phone'] ?? null,
                    'password' => Hash::make($userData['password']),
                    'shop_id' => $employeeData['shop_id'] ?? null,
                    'warehouse_id' => $employeeData['warehouse_id'] ?? null,
                    'is_active' => $employeeData['is_active'] ?? true,
                ]);
                $user->syncRoles([$userData['role']]);
                $employeeData['user_id'] = $user->id;
                $employeeData['email'] = $userData['email'];
            } elseif ($employee->user) {
                $employee->user->update([
                    'name' => trim($employeeData['first_name'].' '.($employeeData['last_name'] ?? '')),
                    'phone' => $employeeData['phone'] ?? null,
                    'shop_id' => $employeeData['shop_id'] ?? null,
                    'warehouse_id' => $employeeData['warehouse_id'] ?? null,
                    'is_active' => $employeeData['is_active'] ?? true,
                ]);
                if (! empty($userData['role'])) {
                    $employee->user->syncRoles([$userData['role']]);
                }
                if (! empty($userData['password'])) {
                    $employee->user->update(['password' => Hash::make($userData['password'])]);
                }
            }

            $employee->update($employeeData);
            $this->upsertSalary($employee, $salaryData);

            return $employee->fresh(['currentSalary', 'shop', 'warehouse', 'user.roles']);
        });
    }

    private function upsertSalary(Employee $employee, array $salaryData): void
    {
        $current = $employee->currentSalary;

        $payload = [
            'basic_salary' => $salaryData['basic_salary'],
            'housing_allowance' => $salaryData['housing_allowance'] ?? 0,
            'transport_allowance' => $salaryData['transport_allowance'] ?? 0,
            'other_allowance' => $salaryData['other_allowance'] ?? 0,
            'payment_method' => $salaryData['payment_method'] ?? 'bank',
            'bank_name' => $salaryData['bank_name'] ?? null,
            'account_number' => $salaryData['account_number'] ?? null,
            'effective_from' => $salaryData['effective_from'] ?? now()->toDateString(),
        ];

        if (! $current) {
            EmployeeSalary::create(array_merge($payload, ['employee_id' => $employee->id]));

            return;
        }

        $changed = collect($payload)->except('effective_from')->some(
            fn ($value, $key) => (string) $current->{$key} !== (string) $value
        );

        if (! $changed) {
            return;
        }

        $current->update(['effective_to' => now()->subDay()->toDateString()]);
        EmployeeSalary::create(array_merge($payload, ['employee_id' => $employee->id]));
    }
}

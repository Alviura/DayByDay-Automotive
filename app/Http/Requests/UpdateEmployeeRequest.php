<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employees.manage');
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $userId = $employee?->user_id;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'kra_pin' => ['nullable', 'string', 'max:20'],
            'nssf_number' => ['nullable', 'string', 'max:30'],
            'shif_number' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'job_title' => ['required', 'string', 'max:100'],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'casual'])],
            'hire_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'station_type' => ['required', Rule::in(['shop', 'warehouse', 'field', 'head_office'])],
            'shop_id' => ['nullable', 'exists:shops,id', 'required_if:station_type,shop'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id', 'required_if:station_type,warehouse'],
            'is_active' => ['boolean'],
            'create_user' => ['boolean'],
            'user_email' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('create_user') && ! $userId),
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'user_password' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('create_user') && ! $userId),
                'string',
                'min:8',
                'confirmed',
            ],
            'user_role' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('create_user') || $userId),
                'string',
                'exists:roles,name',
            ],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'housing_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowance' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['bank', 'cash', 'mpesa'])],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'effective_from' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->station_type !== 'shop') {
            $this->merge(['shop_id' => null]);
        }
        if ($this->station_type !== 'warehouse') {
            $this->merge(['warehouse_id' => null]);
        }
    }
}

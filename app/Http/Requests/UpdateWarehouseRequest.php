<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('warehouses.manage');
    }

    public function rules(): array
    {
        $warehouse = $this->route('warehouse');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('warehouses', 'code')->ignore($warehouse),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.alpha_dash' => 'The warehouse code may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::exists('roles', 'name')],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->role === 'Shop Manager' && ! $this->shop_id) {
                $validator->errors()->add('shop_id', 'Shop Managers must be assigned to a shop.');
            }

            if ($this->role === 'Warehouse Manager' && ! $this->warehouse_id) {
                $validator->errors()->add('warehouse_id', 'Warehouse Managers must be assigned to a warehouse.');
            }
        });
    }
}

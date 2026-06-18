<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:product_names,name'],
            'is_active' => ['boolean'],
        ];
    }
}

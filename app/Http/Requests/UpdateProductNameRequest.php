<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data.manage');
    }

    public function rules(): array
    {
        $productName = $this->route('product_name');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_names', 'name')->ignore($productName),
            ],
            'is_active' => ['boolean'],
        ];
    }
}

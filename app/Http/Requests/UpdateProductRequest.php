<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.edit');
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'part_number' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'part_number')->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'product_name_id' => ['nullable', 'exists:product_names,id'],
            'vehicle_make_id' => ['nullable', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => array_filter([
                'nullable',
                $this->vehicle_make_id
                    ? Rule::exists('vehicle_models', 'id')->where('vehicle_make_id', $this->vehicle_make_id)
                    : null,
            ]),
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'barcode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('products', 'barcode')->ignore($product->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
            'vehicle_model_ids' => ['nullable', 'array'],
            'vehicle_model_ids.*' => ['integer', 'exists:vehicle_models,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->vehicle_make_id) {
            $this->merge(['vehicle_model_id' => null]);
        }
    }
}

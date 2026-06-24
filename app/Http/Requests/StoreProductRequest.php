<?php

namespace App\Http\Requests;

use App\Models\ProductName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.create');
    }

    public function rules(): array
    {
        return [
            'part_number' => ['required', 'string', 'max:50', 'unique:products,part_number'],
            'name' => ['nullable', 'string', 'max:255'],
            'product_name_id' => ['required', 'exists:product_names,id'],
            'vehicle_make_id' => ['nullable', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => array_filter([
                'nullable',
                $this->vehicle_make_id
                    ? Rule::exists('vehicle_models', 'id')->where('vehicle_make_id', $this->vehicle_make_id)
                    : null,
            ]),
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'min_selling_price' => ['nullable', 'numeric', 'min:0'],
            'max_selling_price' => ['nullable', 'numeric', 'min:0', 'gte:min_selling_price'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'width' => ['nullable', 'numeric', 'gt:0', 'required_with:length,height'],
            'length' => ['nullable', 'numeric', 'gt:0', 'required_with:width,height'],
            'height' => ['nullable', 'numeric', 'gt:0', 'required_with:width,length'],
            'quantity_per_packet' => ['nullable', 'numeric', 'min:0.01'],
            'supplier_sell_as' => ['nullable', 'string', 'in:piece,pair,set'],
            'units_per_supplier_unit' => ['nullable', 'numeric', 'min:1'],
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

        if ($this->product_name_id) {
            $productName = ProductName::find($this->product_name_id);
            if ($productName) {
                $this->merge(['name' => $productName->name]);
            }
        }

        $this->merge([
            'width' => $this->filled('width') ? $this->width : null,
            'length' => $this->filled('length') ? $this->length : null,
            'height' => $this->filled('height') ? $this->height : null,
            'quantity_per_packet' => $this->filled('quantity_per_packet') ? $this->quantity_per_packet : 1,
            'supplier_sell_as' => $this->input('supplier_sell_as', 'piece'),
            'units_per_supplier_unit' => $this->filled('units_per_supplier_unit') ? $this->units_per_supplier_unit : 1,
        ]);
    }
}

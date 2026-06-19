<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inventory.adjust');
    }

    public function rules(): array
    {
        return [
            'location_type' => ['required', Rule::in(['warehouse', 'shop'])],
            'location_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', Rule::in(['damaged', 'lost', 'count_variance', 'correction', 'other'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $locationType = $this->location_type;
        $locationId = $this->location_id;

        if ($locationType === 'warehouse') {
            $this->merge([
                'location_id' => $locationId,
                '_location_rule' => 'exists:warehouses,id',
            ]);
        } elseif ($locationType === 'shop') {
            $this->merge([
                'location_id' => $locationId,
                '_location_rule' => 'exists:shops,id',
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->location_type;
            $id = $this->location_id;

            if ($type === 'warehouse' && ! \App\Models\Warehouse::whereKey($id)->exists()) {
                $validator->errors()->add('location_id', 'The selected warehouse is invalid.');
            }

            if ($type === 'shop' && ! \App\Models\Shop::whereKey($id)->exists()) {
                $validator->errors()->add('location_id', 'The selected shop is invalid.');
            }
        });
    }

    public function locationModel(): \Illuminate\Database\Eloquent\Model
    {
        return $this->location_type === 'warehouse'
            ? \App\Models\Warehouse::findOrFail($this->location_id)
            : \App\Models\Shop::findOrFail($this->location_id);
    }
}

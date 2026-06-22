<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('returns.create');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.condition' => ['required', Rule::in(['good', 'damaged'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $warehouse = Warehouse::find($this->warehouse_id);

            if (! $warehouse) {
                return;
            }

            $inventory = app(InventoryService::class);

            foreach ($this->items ?? [] as $index => $line) {
                $product = Product::find($line['product_id'] ?? null);

                if (! $product) {
                    continue;
                }

                $qty = (float) $line['quantity'];
                $available = $inventory->available($product, $warehouse);

                if ($qty > $available) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Insufficient stock for {$product->part_number}. Available: ".number_format($available, 2)
                    );
                }
            }
        });
    }
}

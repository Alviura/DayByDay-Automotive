<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transfers.request');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['warehouse_to_shop', 'inter_shop'])],
            'source_id' => ['required', 'integer', 'min:1'],
            'destination_id' => ['required', 'integer', 'min:1', 'different:source_id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.requested_quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->type;

            if ($type === 'warehouse_to_shop') {
                if (! Warehouse::whereKey($this->source_id)->exists()) {
                    $validator->errors()->add('source_id', 'Select a valid warehouse as source.');
                }
                if (! Shop::whereKey($this->destination_id)->exists()) {
                    $validator->errors()->add('destination_id', 'Select a valid shop as destination.');
                }
            }

            if ($type === 'inter_shop') {
                if (! Shop::whereKey($this->source_id)->exists()) {
                    $validator->errors()->add('source_id', 'Select a valid source shop.');
                }
                if (! Shop::whereKey($this->destination_id)->exists()) {
                    $validator->errors()->add('destination_id', 'Select a valid destination shop.');
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $inventory = app(InventoryService::class);
            $source = $this->sourceModel();

            foreach ($this->items as $index => $item) {
                $product = Product::find($item['product_id']);
                if (! $product) {
                    continue;
                }

                $available = $inventory->available($product, $source);
                $requested = (float) $item['requested_quantity'];

                if ($requested > $available + 0.001) {
                    $validator->errors()->add(
                        "items.{$index}.requested_quantity",
                        "{$product->part_number}: only ".number_format($available, 2).' available at source (requested '.number_format($requested, 2).').'
                    );
                }
            }
        });
    }

    public function sourceModel(): Warehouse|Shop
    {
        return $this->type === 'warehouse_to_shop'
            ? Warehouse::findOrFail($this->source_id)
            : Shop::findOrFail($this->source_id);
    }

    public function destinationModel(): Shop
    {
        return Shop::findOrFail($this->destination_id);
    }
}

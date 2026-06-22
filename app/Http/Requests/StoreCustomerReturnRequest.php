<?php

namespace App\Http\Requests;

use App\Models\ReturnRecord;
use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('returns.create');
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.condition' => ['required', Rule::in(['good', 'damaged'])],
            'items.*.restock' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sale = Sale::with('items')->find($this->sale_id);

            if (! $sale || $sale->status !== 'completed') {
                $validator->errors()->add('sale_id', 'Returns can only be created against completed sales.');

                return;
            }

            foreach ($this->items ?? [] as $index => $line) {
                $saleItem = $sale->items->firstWhere('product_id', $line['product_id'] ?? null);

                if (! $saleItem) {
                    $validator->errors()->add("items.{$index}.product_id", 'Product was not part of this sale.');

                    continue;
                }

                $sold = (float) $saleItem->quantity;
                $alreadyReturned = ReturnRecord::returnedQuantityForSaleProduct(
                    $sale->id,
                    (int) $line['product_id']
                );
                $remaining = $sold - $alreadyReturned;

                if ((float) $line['quantity'] > $remaining) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        'Return quantity exceeds remaining returnable quantity ('.number_format(max(0, $remaining), 2).').'
                    );
                }
            }
        });
    }

    public function sale(): Sale
    {
        return Sale::with(['shop', 'items'])->findOrFail($this->sale_id);
    }
}

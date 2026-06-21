<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.damaged_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $received = \App\Models\GoodsReceiptNoteItem::normalizeQuantity($item['received_quantity'] ?? 0);
                $damaged = \App\Models\GoodsReceiptNoteItem::normalizeQuantity($item['damaged_quantity'] ?? 0);

                if ($damaged > $received) {
                    $validator->errors()->add(
                        "items.{$index}.damaged_quantity",
                        'Damaged quantity cannot exceed received quantity.'
                    );
                }
            }
        });
    }
}

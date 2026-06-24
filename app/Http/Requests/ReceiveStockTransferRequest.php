<?php

namespace App\Http\Requests;

use App\Services\StockTransferAccessService;
use Illuminate\Foundation\Http\FormRequest;

class ReceiveStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $transfer = $this->route('stock_transfer');

        if (! $user->can('transfers.receive') || ! $transfer) {
            return false;
        }

        return app(StockTransferAccessService::class)->canReceive($user, $transfer);
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.damaged_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

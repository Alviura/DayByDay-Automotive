<?php

namespace App\Http\Requests;

use App\Services\StockTransferAccessService;
use Illuminate\Foundation\Http\FormRequest;

class DispatchTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $transfer = $this->route('stock_transfer');

        if (! $user->can('transfers.dispatch') || ! $transfer) {
            return false;
        }

        return app(StockTransferAccessService::class)->canDispatch($user, $transfer);
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Shop;
use App\Models\Warehouse;
use App\Services\TransferRequestAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(TransferRequestAccessService::class)->canCreate($this->user());
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
            $access = app(TransferRequestAccessService::class);
            $user = $this->user();
            $scopedShopId = $access->scopedShopId($user);
            $isAdmin = $access->isAdministrator($user);

            if (! $isAdmin && ! $scopedShopId) {
                $validator->errors()->add('destination_id', 'You must be assigned to a shop to create transfer requests.');

                return;
            }

            if ($type === 'warehouse_to_shop') {
                if (! Warehouse::whereKey($this->source_id)->exists()) {
                    $validator->errors()->add('source_id', 'Select a valid warehouse as source.');
                }
            }

            if ($type === 'inter_shop') {
                if (! Shop::whereKey($this->source_id)->exists()) {
                    $validator->errors()->add('source_id', 'Select a valid source shop.');
                }
                if ((int) $this->source_id === (int) $this->destination_id) {
                    $validator->errors()->add('source_id', 'Choose a different shop as the source.');
                }
            }

            if (! Shop::whereKey($this->destination_id)->exists()) {
                $validator->errors()->add('destination_id', 'Select a valid destination shop.');
            }

            if (! $isAdmin && (int) $this->destination_id !== $scopedShopId) {
                $validator->errors()->add('destination_id', 'Requests must be for your assigned shop.');
            }

            if (! $isAdmin && $type === 'inter_shop' && (int) $this->source_id === $scopedShopId) {
                $validator->errors()->add('source_id', 'Choose a different shop as the source.');
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

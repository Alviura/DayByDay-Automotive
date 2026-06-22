<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(private InventoryService $inventory) {}

    public function process(ReturnRecord $return, ?User $user = null): ReturnRecord
    {
        if ($return->status === 'completed') {
            throw new \InvalidArgumentException('This return has already been processed.');
        }

        $user ??= auth()->user();
        $return->load(['items.product', 'shop', 'warehouse', 'sale']);

        return DB::transaction(function () use ($return, $user) {
            $refundTotal = 0;

            foreach ($return->items as $item) {
                $qty = (float) $item->quantity;

                if ($qty <= 0) {
                    continue;
                }

                if ($return->type === 'customer') {
                    $refundTotal += $item->lineRefund();

                    if ($item->restock && $item->condition === 'good' && $return->shop) {
                        $balance = $this->inventory->getBalance($item->product, $return->shop);
                        $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? $item->unit_price ?? 0);

                        $this->inventory->record(
                            $item->product,
                            $return->shop,
                            'customer_return',
                            $qty,
                            $unitCost,
                            $return,
                            $return->return_number,
                            'Customer return — '.$return->reason,
                            $user
                        );
                    }
                } else {
                    if (! $return->warehouse) {
                        throw new InventoryException('Warehouse is required for supplier returns.');
                    }

                    $available = $this->inventory->available($item->product, $return->warehouse);

                    if ($qty > $available) {
                        throw new InventoryException(
                            "Insufficient stock for {$item->product->part_number}. Available: ".number_format($available, 2)
                        );
                    }

                    $balance = $this->inventory->getBalance($item->product, $return->warehouse);
                    $unitCost = (float) ($balance?->average_cost ?? $item->product->cost_price ?? 0);

                    $this->inventory->record(
                        $item->product,
                        $return->warehouse,
                        'supplier_return',
                        -$qty,
                        $unitCost,
                        $return,
                        $return->return_number,
                        'Supplier return — '.$return->reason,
                        $user
                    );
                }
            }

            $return->update([
                'status' => 'completed',
                'refund_amount' => $return->type === 'customer' ? $refundTotal : 0,
                'processed_by' => $user->id,
            ]);

            return $return->fresh(['items.product', 'shop', 'warehouse', 'supplier', 'sale']);
        });
    }
}

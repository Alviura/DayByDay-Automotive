<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * quantity_reserved is incremented by reserve() for:
     * - POS held sales (M15)
     * - Approved transfer requests awaiting dispatch (M14)
     */
    public function record(
        Product $product,
        Model $location,
        string $transactionType,
        float $quantity,
        ?float $unitCost = null,
        ?Model $reference = null,
        ?string $referenceNumber = null,
        ?string $notes = null,
        ?User $user = null,
    ): StockLedger {
        if ($quantity == 0) {
            throw new InventoryException('Quantity must not be zero.');
        }

        return DB::transaction(function () use (
            $product, $location, $transactionType, $quantity, $unitCost,
            $reference, $referenceNumber, $notes, $user
        ) {
            $balance = $this->lockBalance($product, $location);
            $newOnHand = (float) $balance->quantity_on_hand + $quantity;

            if ($newOnHand < 0) {
                throw new InventoryException(
                    "Insufficient stock for {$product->part_number}. Available: {$balance->quantity_on_hand}, requested change: {$quantity}."
                );
            }

            if ($quantity > 0 && $unitCost !== null) {
                $balance->average_cost = $this->calculateWeightedAverage(
                    (float) $balance->quantity_on_hand,
                    (float) $balance->average_cost,
                    $quantity,
                    $unitCost
                );
            } elseif ((float) $balance->average_cost === 0.0 && $unitCost !== null) {
                $balance->average_cost = $unitCost;
            }

            $balance->quantity_on_hand = $newOnHand;
            $balance->save();

            return StockLedger::create([
                'product_id' => $product->id,
                'location_type' => $location->getMorphClass(),
                'location_id' => $location->getKey(),
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'balance_after' => $newOnHand,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'reference_number' => $referenceNumber,
                'user_id' => $user?->id ?? auth()->id(),
                'notes' => $notes,
            ]);
        });
    }

    public function reserve(Product $product, Model $location, float $quantity): StockBalance
    {
        if ($quantity <= 0) {
            throw new InventoryException('Reserve quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $location, $quantity) {
            $balance = $this->lockBalance($product, $location);

            if ($this->available($product, $location, $balance) < $quantity) {
                throw new InventoryException('Not enough available stock to reserve.');
            }

            $balance->quantity_reserved = (float) $balance->quantity_reserved + $quantity;
            $balance->save();

            return $balance->fresh();
        });
    }

    public function release(Product $product, Model $location, float $quantity): StockBalance
    {
        if ($quantity <= 0) {
            throw new InventoryException('Release quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $location, $quantity) {
            $balance = $this->lockBalance($product, $location);

            if ((float) $balance->quantity_reserved < $quantity) {
                throw new InventoryException('Cannot release more than the reserved quantity.');
            }

            $balance->quantity_reserved = (float) $balance->quantity_reserved - $quantity;
            $balance->save();

            return $balance->fresh();
        });
    }

    public function available(Product $product, Model $location, ?StockBalance $balance = null): float
    {
        $balance ??= $this->getBalance($product, $location);

        if (! $balance) {
            return 0;
        }

        return max(0, (float) $balance->quantity_on_hand - (float) $balance->quantity_reserved);
    }

    public function getBalance(Product $product, Model $location): ?StockBalance
    {
        return StockBalance::query()
            ->where('product_id', $product->id)
            ->forLocation($location)
            ->first();
    }

    public function getOrCreateBalance(Product $product, Model $location): StockBalance
    {
        return StockBalance::firstOrCreate(
            [
                'product_id' => $product->id,
                'location_type' => $location->getMorphClass(),
                'location_id' => $location->getKey(),
            ],
            [
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'average_cost' => $product->cost_price ?? 0,
            ]
        );
    }

    public function valuation(Model $location): array
    {
        $balances = StockBalance::query()
            ->with('product')
            ->forLocation($location)
            ->where('quantity_on_hand', '>', 0)
            ->get();

        $totalValue = $balances->sum(fn (StockBalance $b) => $b->stockValue());
        $totalUnits = $balances->sum('quantity_on_hand');
        $skuCount = $balances->count();

        return [
            'balances' => $balances,
            'total_value' => $totalValue,
            'total_units' => $totalUnits,
            'sku_count' => $skuCount,
        ];
    }

    public function valuationForAllLocations(): Collection
    {
        $warehouses = \App\Models\Warehouse::active()->orderBy('name')->get();
        $shops = \App\Models\Shop::active()->orderBy('name')->get();

        return collect()
            ->merge($warehouses->map(fn ($w) => ['location' => $w, 'type' => 'Warehouse', ...$this->valuation($w)]))
            ->merge($shops->map(fn ($s) => ['location' => $s, 'type' => 'Shop', ...$this->valuation($s)]));
    }

    public function postAdjustment(StockAdjustment $adjustment, ?User $user = null): void
    {
        if ($adjustment->status === 'approved') {
            throw new InventoryException('This adjustment has already been posted.');
        }

        $adjustment->load(['items.product', 'location']);

        if (! $adjustment->location) {
            throw new InventoryException('Adjustment location is missing.');
        }

        DB::transaction(function () use ($adjustment, $user) {
            foreach ($adjustment->items as $item) {
                if ((float) $item->difference == 0) {
                    continue;
                }

                $unitCost = $item->unit_cost ?? $item->product->cost_price ?? 0;

                $this->record(
                    $item->product,
                    $adjustment->location,
                    'adjustment',
                    (float) $item->difference,
                    (float) $unitCost,
                    $adjustment,
                    $adjustment->adjustment_number,
                    "Adjustment: {$adjustment->reasonLabel()}",
                    $user
                );
            }
        });
    }

    public function openingBalance(
        Product $product,
        Model $location,
        float $quantity,
        float $unitCost,
        ?string $notes = null,
        ?User $user = null
    ): StockLedger {
        return $this->record(
            $product,
            $location,
            'opening_balance',
            $quantity,
            $unitCost,
            null,
            null,
            $notes ?? 'Opening balance',
            $user
        );
    }

    private function lockBalance(Product $product, Model $location): StockBalance
    {
        return StockBalance::query()
            ->where('product_id', $product->id)
            ->where('location_type', $location->getMorphClass())
            ->where('location_id', $location->getKey())
            ->lockForUpdate()
            ->first() ?? $this->getOrCreateBalance($product, $location);
    }

    private function calculateWeightedAverage(
        float $currentQty,
        float $currentAvg,
        float $incomingQty,
        float $incomingCost
    ): float {
        $totalQty = $currentQty + $incomingQty;

        if ($totalQty <= 0) {
            return $incomingCost;
        }

        return (($currentQty * $currentAvg) + ($incomingQty * $incomingCost)) / $totalQty;
    }
}

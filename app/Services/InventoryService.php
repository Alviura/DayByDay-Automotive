<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\QuotationItem;
use App\Models\Shop;
use App\Models\StockAdjustment;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\GoodsReceiptNoteItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(private GlPostingService $gl) {}

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
            } elseif ($quantity < 0 && $unitCost !== null && $transactionType === 'purchase_receipt_void') {
                $balance->average_cost = $this->reverseWeightedAverage(
                    (float) $balance->quantity_on_hand,
                    (float) $balance->average_cost,
                    $quantity,
                    $unitCost
                );
            } elseif ((float) $balance->average_cost === 0.0 && $unitCost !== null && $quantity > 0) {
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

            $this->gl->postStockAdjustment($adjustment->fresh(['items.product']), $user);
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

    public function syncProductCostFromStock(Product $product): void
    {
        $balances = StockBalance::query()
            ->where('product_id', $product->id)
            ->where('quantity_on_hand', '>', 0)
            ->get();

        if ($balances->isNotEmpty()) {
            $totalQty = (float) $balances->sum('quantity_on_hand');
            $totalValue = $balances->sum(fn (StockBalance $b) => (float) $b->quantity_on_hand * (float) $b->average_cost);

            if ($totalQty > 0) {
                $product->update(['cost_price' => round($totalValue / $totalQty, 2)]);

                return;
            }
        }

        $lastReceipt = StockLedger::query()
            ->where('product_id', $product->id)
            ->where('transaction_type', 'purchase_receipt')
            ->where('quantity', '>', 0)
            ->latest()
            ->first();

        if ($lastReceipt && $lastReceipt->unit_cost !== null) {
            $product->update(['cost_price' => (float) $lastReceipt->unit_cost]);
        }
    }

    /**
     * @return array{
     *     balances: \Illuminate\Support\Collection,
     *     movements: \Illuminate\Support\Collection,
     *     incoming: array{units: float, lines: int, value: float, items: \Illuminate\Support\Collection},
     *     recentReceipts: \Illuminate\Support\Collection,
     *     poLines: \Illuminate\Support\Collection,
     *     openPoLines: \Illuminate\Support\Collection,
     *     quotationItems: \Illuminate\Support\Collection,
     *     totals: array{on_hand: float, reserved: float, available: float, value: float, warehouse_qty: float, shop_qty: float, ledger_avg: float}
     * }
     */
    public function productShowContext(Product $product): array
    {
        $balances = StockBalance::query()
            ->with('location')
            ->where('product_id', $product->id)
            ->orderByDesc('quantity_on_hand')
            ->get();

        $movements = StockLedger::query()
            ->with(['location', 'user'])
            ->where('product_id', $product->id)
            ->latest()
            ->limit(25)
            ->get();

        $incoming = $this->incomingFromPurchaseOrders($product->id);

        $recentReceipts = GoodsReceiptNoteItem::query()
            ->with(['goodsReceiptNote.warehouse', 'goodsReceiptNote.purchaseOrder'])
            ->where('product_id', $product->id)
            ->whereHas('goodsReceiptNote', fn ($q) => $q->whereIn('status', ['posted', 'voided']))
            ->latest()
            ->limit(15)
            ->get();

        $poLines = PurchaseOrderItem::query()
            ->with(['purchaseOrder.supplier', 'purchaseOrder.quotationSeries'])
            ->where('product_id', $product->id)
            ->whereHas('purchaseOrder')
            ->latest()
            ->limit(20)
            ->get();

        $openPoLines = PurchaseOrderItem::query()
            ->with(['purchaseOrder.supplier'])
            ->where('product_id', $product->id)
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['sent', 'partially_received']))
            ->latest()
            ->get()
            ->filter(fn (PurchaseOrderItem $item) => $item->remainingQuantity() > 0.001)
            ->values();

        $quotationItems = QuotationItem::query()
            ->with(['series.supplier'])
            ->where('product_id', $product->id)
            ->latest()
            ->limit(15)
            ->get();

        $warehouseMorph = Warehouse::class;
        $shopMorph = Shop::class;
        $positiveBalances = $balances->where('quantity_on_hand', '>', 0);

        $totals = [
            'on_hand' => (float) $balances->sum('quantity_on_hand'),
            'reserved' => (float) $balances->sum('quantity_reserved'),
            'available' => (float) $balances->sum('quantity_available'),
            'value' => (float) $balances->sum(fn (StockBalance $b) => $b->stockValue()),
            'warehouse_qty' => (float) $balances->where('location_type', $warehouseMorph)->sum('quantity_on_hand'),
            'shop_qty' => (float) $balances->where('location_type', $shopMorph)->sum('quantity_on_hand'),
            'ledger_avg' => $positiveBalances->isNotEmpty()
                ? round($positiveBalances->sum(fn (StockBalance $b) => $b->stockValue())
                    / $positiveBalances->sum('quantity_on_hand'), 2)
                : 0,
        ];

        return compact('balances', 'movements', 'incoming', 'recentReceipts', 'poLines', 'openPoLines', 'quotationItems', 'totals');
    }

    public function paginatedProductStockSummary(Request $request): LengthAwarePaginator
    {
        $warehouseMorph = Warehouse::class;
        $shopMorph = Shop::class;

        $balanceFilters = StockBalance::query()
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->location_type && $request->location_id, function ($q) use ($request) {
                $morph = $request->location_type === 'warehouse' ? Warehouse::class : Shop::class;
                $q->where('location_type', $morph)->where('location_id', $request->location_id);
            });

        $aggregated = (clone $balanceFilters)
            ->select('product_id')
            ->selectRaw('SUM(quantity_on_hand) as total_on_hand')
            ->selectRaw('SUM(quantity_reserved) as total_reserved')
            ->selectRaw('SUM(quantity_available) as total_available')
            ->selectRaw('SUM(quantity_on_hand * average_cost) as total_value')
            ->selectRaw('SUM(CASE WHEN location_type = ? THEN quantity_on_hand ELSE 0 END) as warehouse_qty', [$warehouseMorph])
            ->selectRaw('SUM(CASE WHEN location_type = ? THEN quantity_on_hand ELSE 0 END) as shop_qty', [$shopMorph])
            ->groupBy('product_id');

        return Product::query()
            ->joinSub($aggregated, 'stock_agg', fn ($join) => $join->on('products.id', '=', 'stock_agg.product_id'))
            ->with(['unit', 'stockBalances' => fn ($q) => $q->with('location')->orderByDesc('quantity_on_hand')])
            ->when($request->filter === 'in_stock', fn ($q) => $q->where('stock_agg.total_on_hand', '>', 0))
            ->when($request->filter === 'out_of_stock', fn ($q) => $q->where('stock_agg.total_on_hand', '<=', 0))
            ->when($request->filter === 'low_stock', fn ($q) => $q
                ->where('products.reorder_level', '>', 0)
                ->whereColumn('stock_agg.total_on_hand', '<=', 'products.reorder_level')
                ->where('stock_agg.total_on_hand', '>', 0))
            ->when($request->sort === 'product', fn ($q) => $q->orderBy('products.name'))
            ->when($request->sort === 'value', fn ($q) => $q->orderByDesc('stock_agg.total_value'))
            ->when(
                $request->sort === 'qty' || ! in_array($request->sort, ['product', 'value'], true),
                fn ($q) => $q->orderByDesc('stock_agg.total_on_hand')
            )
            ->select([
                'products.*',
                'stock_agg.total_on_hand',
                'stock_agg.total_reserved',
                'stock_agg.total_available',
                'stock_agg.total_value',
                'stock_agg.warehouse_qty',
                'stock_agg.shop_qty',
            ])
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return array{skus: int, units: float, reserved: float, value: float, low_stock: int, incoming_units: float, incoming_lines: int}
     */
    public function indexStats(): array
    {
        $incoming = $this->incomingFromPurchaseOrders();

        $aggregated = StockBalance::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity_on_hand) as total_on_hand')
            ->groupBy('product_id');

        $skus = (int) DB::query()
            ->fromSub($aggregated, 'agg')
            ->where('total_on_hand', '>', 0)
            ->count();

        $lowStock = Product::query()
            ->joinSub($aggregated, 'agg', fn ($join) => $join->on('products.id', '=', 'agg.product_id'))
            ->where('products.reorder_level', '>', 0)
            ->whereColumn('agg.total_on_hand', '<=', 'products.reorder_level')
            ->where('agg.total_on_hand', '>', 0)
            ->count();

        return [
            'skus' => $skus,
            'units' => (float) StockBalance::sum('quantity_on_hand'),
            'reserved' => (float) StockBalance::sum('quantity_reserved'),
            'value' => StockBalance::selectRaw('SUM(quantity_on_hand * average_cost) as total')->value('total') ?? 0,
            'low_stock' => $lowStock,
            'incoming_units' => $incoming['units'],
            'incoming_lines' => $incoming['lines'],
        ];
    }

    /**
     * @return array{units: float, lines: int, value: float}
     */
    public function incomingFromPurchaseOrders(?int $productId = null): array
    {
        $query = PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['sent', 'partially_received']))
            ->with(['product.unit', 'purchaseOrder.supplier']);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $lines = $query->get()->filter(fn (PurchaseOrderItem $item) => $item->remainingQuantity() > 0.001);

        return [
            'units' => round((float) $lines->sum(fn (PurchaseOrderItem $item) => $item->remainingQuantity()), 2),
            'lines' => $lines->count(),
            'value' => round((float) $lines->sum(fn (PurchaseOrderItem $item) => $item->remainingQuantity() * (float) $item->unit_cost), 2),
            'items' => $lines,
        ];
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

    private function reverseWeightedAverage(
        float $currentQty,
        float $currentAvg,
        float $outgoingQty,
        float $layerCost
    ): float {
        $newQty = $currentQty + $outgoingQty;

        if ($newQty <= 0) {
            return 0;
        }

        return max(0, (($currentQty * $currentAvg) + ($outgoingQty * $layerCost)) / $newQty);
    }
}

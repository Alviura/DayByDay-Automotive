<?php

namespace App\Services;

use App\Models\GoodsReceiptNote;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\StockAdjustment;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\TransferRequest;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;

class LocationOverviewService
{
    public function __construct(private InventoryService $inventory) {}

    /**
     * @return array{
     *     balances: \Illuminate\Support\Collection,
     *     movements: \Illuminate\Support\Collection,
     *     lowStock: \Illuminate\Support\Collection,
     *     totals: array{on_hand: float, reserved: float, available: float, value: float, sku_count: int, low_stock_count: int},
     *     location_type: string
     * }
     */
    public function inventoryContext(Model $location): array
    {
        $valuation = $this->inventory->valuation($location);

        $balances = StockBalance::query()
            ->with(['product.unit'])
            ->forLocation($location)
            ->orderByDesc('quantity_on_hand')
            ->get();

        $movements = StockLedger::query()
            ->with(['product.unit', 'user'])
            ->forLocation($location)
            ->latest()
            ->limit(25)
            ->get();

        $positiveBalances = $balances->where('quantity_on_hand', '>', 0);

        $lowStock = $positiveBalances
            ->filter(function (StockBalance $balance) {
                $reorder = (float) ($balance->product?->reorder_level ?? 0);

                return $reorder > 0 && (float) $balance->quantity_on_hand <= $reorder;
            })
            ->sortBy('quantity_on_hand')
            ->values();

        $totals = [
            'on_hand' => (float) $balances->sum('quantity_on_hand'),
            'reserved' => (float) $balances->sum('quantity_reserved'),
            'available' => (float) $balances->sum('quantity_available'),
            'value' => (float) $valuation['total_value'],
            'sku_count' => (int) $valuation['sku_count'],
            'low_stock_count' => $lowStock->count(),
        ];

        return [
            'balances' => $balances,
            'movements' => $movements,
            'lowStock' => $lowStock,
            'totals' => $totals,
            'location_type' => $location instanceof Warehouse ? 'warehouse' : 'shop',
        ];
    }

    /**
     * @return array{
     *     stats: array{held: int, completed_today: int, today_total: float, avg_ticket_today: float, completed_total: int},
     *     recentSales: \Illuminate\Support\Collection,
     *     heldSales: \Illuminate\Support\Collection,
     *     transfers: \Illuminate\Support\Collection,
     *     returns: \Illuminate\Support\Collection,
     *     adjustments: \Illuminate\Support\Collection
     * }
     */
    public function shopActivity(Shop $shop): array
    {
        $morph = $shop->getMorphClass();

        $completedTodayQuery = Sale::query()
            ->where('shop_id', $shop->id)
            ->where('status', 'completed')
            ->whereDate('sold_at', today());

        $completedToday = (clone $completedTodayQuery)->count();
        $todayTotal = (float) (clone $completedTodayQuery)->sum('total');

        $stats = [
            'held' => Sale::where('shop_id', $shop->id)->where('status', 'held')->count(),
            'completed_today' => $completedToday,
            'today_total' => $todayTotal,
            'avg_ticket_today' => $completedToday > 0 ? round($todayTotal / $completedToday, 2) : 0.0,
            'completed_total' => Sale::where('shop_id', $shop->id)->where('status', 'completed')->count(),
        ];

        $recentSales = Sale::query()
            ->where('shop_id', $shop->id)
            ->where('status', 'completed')
            ->with(['cashier', 'customerAccount'])
            ->withCount('items')
            ->latest('sold_at')
            ->limit(10)
            ->get();

        $heldSales = Sale::query()
            ->where('shop_id', $shop->id)
            ->where('status', 'held')
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();

        $transfers = $this->transfersForLocation($morph, $shop->id);
        $returns = $this->returnsForShop($shop);
        $adjustments = $this->adjustmentsForLocation($morph, $shop->id);

        return compact('stats', 'recentSales', 'heldSales', 'transfers', 'returns', 'adjustments');
    }

    /**
     * @return array{
     *     transfers: \Illuminate\Support\Collection,
     *     returns: \Illuminate\Support\Collection,
     *     receipts: \Illuminate\Support\Collection,
     *     adjustments: \Illuminate\Support\Collection
     * }
     */
    public function warehouseActivity(Warehouse $warehouse): array
    {
        $morph = $warehouse->getMorphClass();

        $transfers = $this->transfersForLocation($morph, $warehouse->id);

        $returns = ReturnRecord::query()
            ->where('type', 'supplier')
            ->where('warehouse_id', $warehouse->id)
            ->with(['supplier'])
            ->withCount('items')
            ->latest()
            ->limit(8)
            ->get();

        $receipts = GoodsReceiptNote::query()
            ->where('warehouse_id', $warehouse->id)
            ->with(['purchaseOrder', 'quotationSeries'])
            ->withCount('items')
            ->latest('received_at')
            ->limit(8)
            ->get();

        $adjustments = $this->adjustmentsForLocation($morph, $warehouse->id);

        return compact('transfers', 'returns', 'receipts', 'adjustments');
    }

    private function transfersForLocation(string $morph, int $id)
    {
        return TransferRequest::query()
            ->with(['source', 'destination', 'requester'])
            ->withCount('items')
            ->where(function ($q) use ($morph, $id) {
                $q->where(function ($inner) use ($morph, $id) {
                    $inner->where('source_type', $morph)->where('source_id', $id);
                })->orWhere(function ($inner) use ($morph, $id) {
                    $inner->where('destination_type', $morph)->where('destination_id', $id);
                });
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    private function returnsForShop(Shop $shop)
    {
        return ReturnRecord::query()
            ->where('type', 'customer')
            ->where('shop_id', $shop->id)
            ->with(['sale'])
            ->withCount('items')
            ->latest()
            ->limit(8)
            ->get();
    }

    private function adjustmentsForLocation(string $morph, int $id)
    {
        return StockAdjustment::query()
            ->where('location_type', $morph)
            ->where('location_id', $id)
            ->withCount('items')
            ->latest()
            ->limit(8)
            ->get();
    }
}

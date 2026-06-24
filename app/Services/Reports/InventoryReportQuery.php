<?php

namespace App\Services\Reports;

use App\Models\Shop;
use App\Models\StockBalance;
use App\Services\InventoryService;
use App\Services\Reports\Concerns\ScopesSales;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

/**
 * Data contract §4.2 — point-in-time balances; movements by ledger created_at.
 */
class InventoryReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function __construct(private InventoryService $inventory) {}

    public function run(ReportFilters $filters): array
    {
        $locations = collect();

        if (! $filters->shopId) {
            $warehouses = $filters->warehouseId
                ? \App\Models\Warehouse::whereKey($filters->warehouseId)->get()
                : \App\Models\Warehouse::active()->orderBy('name')->get();

            foreach ($warehouses as $warehouse) {
                $val = $this->inventory->valuation($warehouse);
                $locations->push([
                    'type' => 'Warehouse',
                    'name' => $warehouse->name,
                    'sku_count' => $val['sku_count'],
                    'total_units' => $val['total_units'],
                    'total_value' => $val['total_value'],
                ]);
            }
        }

        $shops = $filters->shopId
            ? Shop::whereKey($filters->shopId)->get()
            : Shop::active()->orderBy('name')->get();

        foreach ($shops as $shop) {
            $val = $this->inventory->valuation($shop);
            $locations->push([
                'type' => 'Shop',
                'name' => $shop->name,
                'sku_count' => $val['sku_count'],
                'total_units' => $val['total_units'],
                'total_value' => $val['total_value'],
            ]);
        }

        $lowStockQuery = StockBalance::query()
            ->with(['product:id,part_number,name,reorder_level,supplier_sell_as,units_per_supplier_unit', 'location'])
            ->where('quantity_on_hand', '>', 0)
            ->when($filters->shopId, fn ($q) => $q->where('location_type', Shop::class)->where('location_id', $filters->shopId))
            ->when($filters->warehouseId, fn ($q) => $q->where('location_type', \App\Models\Warehouse::class)->where('location_id', $filters->warehouseId))
            ->whereHas('product', fn ($pq) => $pq->where('reorder_level', '>', 0))
            ->whereRaw('(
                CASE
                    WHEN (SELECT supplier_sell_as FROM products WHERE products.id = stock_balances.product_id) IS NOT NULL
                        AND (SELECT supplier_sell_as FROM products WHERE products.id = stock_balances.product_id) != ?
                        AND COALESCE((SELECT units_per_supplier_unit FROM products WHERE products.id = stock_balances.product_id), 1) > 1
                    THEN FLOOR(stock_balances.quantity_on_hand / (SELECT units_per_supplier_unit FROM products WHERE products.id = stock_balances.product_id))
                    ELSE stock_balances.quantity_on_hand
                END
            ) <= (SELECT reorder_level FROM products WHERE products.id = stock_balances.product_id)', [\App\Enums\SupplierSellAs::Piece->value])
            ->orderBy('quantity_on_hand');

        $lowStock = (clone $lowStockQuery)->limit(50)->get();

        $movements = $this->applyLocationScope(
            \App\Models\StockLedger::query()->whereBetween('created_at', [$filters->from, $filters->to])
        )
            ->selectRaw('transaction_type, COUNT(*) as entries, SUM(ABS(quantity)) as total_qty')
            ->groupBy('transaction_type')
            ->orderBy('transaction_type')
            ->get();

        $summary = [
            'total_value' => $locations->sum('total_value'),
            'total_units' => $locations->sum('total_units'),
            'low_stock_count' => $lowStock->count(),
        ];

        return compact('summary', 'locations', 'lowStock', 'movements');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $lowStockQuery = StockBalance::query()
            ->with(['product', 'location'])
            ->where('quantity_on_hand', '>', 0)
            ->when($filters->shopId, fn ($q) => $q->where('location_type', Shop::class)->where('location_id', $filters->shopId))
            ->when($filters->warehouseId, fn ($q) => $q->where('location_type', \App\Models\Warehouse::class)->where('location_id', $filters->warehouseId))
            ->whereHas('product', fn ($pq) => $pq->where('reorder_level', '>', 0))
            ->whereRaw('(
                CASE
                    WHEN (SELECT supplier_sell_as FROM products WHERE products.id = stock_balances.product_id) IS NOT NULL
                        AND (SELECT supplier_sell_as FROM products WHERE products.id = stock_balances.product_id) != ?
                        AND COALESCE((SELECT units_per_supplier_unit FROM products WHERE products.id = stock_balances.product_id), 1) > 1
                    THEN FLOOR(stock_balances.quantity_on_hand / (SELECT units_per_supplier_unit FROM products WHERE products.id = stock_balances.product_id))
                    ELSE stock_balances.quantity_on_hand
                END
            ) <= (SELECT reorder_level FROM products WHERE products.id = stock_balances.product_id)', [\App\Enums\SupplierSellAs::Piece->value]);

        $rows = (clone $lowStockQuery)->get()->map(function (StockBalance $balance) {
            $product = $balance->product;

            return [
                'Part Number' => $product->part_number,
                'Product' => $product->name,
                'Location' => $balance->location?->name,
                'On Hand (catalog)' => CatalogQuantity::orderQuantityFromStock($product, (float) $balance->quantity_on_hand),
                'Order Unit' => $product->orderUnitLabel(),
                'Stock Pcs' => $balance->quantity_on_hand,
                'Reorder Level' => $product->reorder_level,
            ];
        });

        if ($rows->isEmpty()) {
            $data = $this->run($filters);
            $rows = $data['locations']->map(fn ($loc) => [
                'Type' => $loc['type'],
                'Location' => $loc['name'],
                'SKUs' => $loc['sku_count'],
                'Units' => $loc['total_units'],
                'Value' => number_format($loc['total_value'], 2, '.', ''),
            ]);
        }

        return $this->truncateIfNeeded($rows);
    }

    public function csvHeaders(): array
    {
        return ['Part Number', 'Product', 'Location', 'On Hand (catalog)', 'Order Unit', 'Stock Pcs', 'Reorder Level'];
    }
}

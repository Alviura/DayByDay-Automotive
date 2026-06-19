<?php

namespace App\Services\Reports;

use App\Models\Product;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Support\Collection;

class InventoryReportQuery
{
    public function __construct(private InventoryService $inventory) {}

    public function run(ReportFilters $filters): array
    {
        $locations = collect();

        if (! $filters->shopId) {
            foreach (Warehouse::active()->orderBy('name')->get() as $warehouse) {
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

        $lowStock = StockBalance::query()
            ->with(['product:id,part_number,name,reorder_level', 'location'])
            ->where('quantity_on_hand', '>', 0)
            ->when($filters->shopId, function ($q) use ($filters) {
                $q->where('location_type', Shop::class)->where('location_id', $filters->shopId);
            })
            ->whereHas('product', fn ($pq) => $pq->where('reorder_level', '>', 0))
            ->whereRaw('stock_balances.quantity_on_hand <= (SELECT reorder_level FROM products WHERE products.id = stock_balances.product_id)')
            ->orderBy('quantity_on_hand')
            ->limit(25)
            ->get();

        $movements = StockLedger::query()
            ->whereBetween('created_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('location_type', Shop::class)->where('location_id', $filters->shopId))
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
        $data = $this->run($filters);

        return $data['locations']->map(fn ($row) => [
            'Type' => $row['type'],
            'Location' => $row['name'],
            'SKUs' => $row['sku_count'],
            'Units' => $row['total_units'],
            'Value' => number_format($row['total_value'], 2, '.', ''),
        ]);
    }
}

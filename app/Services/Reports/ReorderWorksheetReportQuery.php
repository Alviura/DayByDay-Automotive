<?php

namespace App\Services\Reports;

use App\Enums\SupplierSellAs;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class ReorderWorksheetReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = $this->lowStockQuery($filters);
        $rows = (clone $query)->with(['product', 'location'])->orderBy('quantity_on_hand')->get();

        return [
            'summary' => ['skus' => $rows->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->lowStockQuery($filters)->with(['product', 'location'])->get()->map(fn (StockBalance $b) => [
                'Part' => $b->product?->part_number,
                'Product' => $b->product?->name,
                'Location' => $b->location?->name,
                'On Hand' => CatalogQuantity::orderQuantityFromStock($b->product, (float) $b->quantity_on_hand),
                'Unit' => $b->product?->orderUnitLabel(),
                'Reorder' => $b->product?->reorder_level,
                'Fitment' => $b->product?->fitmentLabel(),
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Part', 'Product', 'Location', 'On Hand', 'Unit', 'Reorder', 'Fitment'];
    }

    private function lowStockQuery(ReportFilters $filters)
    {
        return StockBalance::query()
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
            ) <= (SELECT reorder_level FROM products WHERE products.id = stock_balances.product_id)', [SupplierSellAs::Piece->value]);
    }
}

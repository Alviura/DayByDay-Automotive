<?php

namespace App\Services\Reports;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class StockTransferDetailReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $rows = StockTransferItem::query()
            ->join('stock_transfers', 'stock_transfer_items.stock_transfer_id', '=', 'stock_transfers.id')
            ->join('products', 'stock_transfer_items.product_id', '=', 'products.id')
            ->whereBetween('stock_transfers.dispatched_at', [$filters->from, $filters->to])
            ->select([
                'stock_transfers.transfer_number',
                'stock_transfers.status',
                'stock_transfers.dispatched_at',
                'products.part_number',
                'products.name as product_name',
                'products.supplier_sell_as',
                'products.units_per_supplier_unit',
                'stock_transfer_items.dispatched_quantity',
                'stock_transfer_items.received_quantity',
                'stock_transfer_items.damaged_quantity',
            ])
            ->orderByDesc('stock_transfers.dispatched_at')
            ->limit(100)
            ->get()
            ->map(function ($row) {
                $product = new \App\Models\Product([
                    'supplier_sell_as' => $row->supplier_sell_as,
                    'units_per_supplier_unit' => $row->units_per_supplier_unit,
                ]);
                $row->dispatched_catalog = CatalogQuantity::orderQuantityFromStock($product, (float) $row->dispatched_quantity);
                $row->received_catalog = CatalogQuantity::orderQuantityFromStock($product, (float) $row->received_quantity);

                return $row;
            });

        return [
            'summary' => ['lines' => $rows->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'Transfer' => $r->transfer_number,
                'Part' => $r->part_number,
                'Dispatched' => $r->dispatched_catalog,
                'Received' => $r->received_catalog,
                'Status' => $r->status,
                'Dispatched At' => $r->dispatched_at,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Transfer', 'Part', 'Dispatched', 'Received', 'Status', 'Dispatched At'];
    }
}

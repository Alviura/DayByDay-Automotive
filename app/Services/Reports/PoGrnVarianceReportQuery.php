<?php

namespace App\Services\Reports;

use App\Models\PurchaseOrderItem;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class PoGrnVarianceReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $rows = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->whereBetween('purchase_orders.order_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->supplierId, fn ($q) => $q->where('purchase_orders.supplier_id', $filters->supplierId))
            ->whereRaw('purchase_order_items.received_quantity != purchase_order_items.quantity')
            ->select([
                'purchase_orders.po_number',
                'products.part_number',
                'products.name as product_name',
                'products.supplier_sell_as',
                'products.units_per_supplier_unit',
                'purchase_order_items.quantity',
                'purchase_order_items.received_quantity',
                'purchase_orders.status',
            ])
            ->orderBy('purchase_orders.order_date')
            ->limit(100)
            ->get()
            ->map(function ($row) {
                $product = new \App\Models\Product([
                    'supplier_sell_as' => $row->supplier_sell_as,
                    'units_per_supplier_unit' => $row->units_per_supplier_unit,
                ]);
                $row->ordered_catalog = CatalogQuantity::orderQuantityFromStock($product, (float) $row->quantity);
                $row->received_catalog = CatalogQuantity::orderQuantityFromStock($product, (float) $row->received_quantity);
                $row->variance = $row->received_catalog - $row->ordered_catalog;

                return $row;
            });

        return [
            'summary' => ['lines' => $rows->count(), 'short_lines' => $rows->where('variance', '<', 0)->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'PO' => $r->po_number,
                'Part' => $r->part_number,
                'Ordered' => $r->ordered_catalog,
                'Received' => $r->received_catalog,
                'Variance' => $r->variance,
                'Status' => $r->status,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['PO', 'Part', 'Ordered', 'Received', 'Variance', 'Status'];
    }
}

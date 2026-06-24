<?php

namespace App\Services\Reports;

use App\Models\PurchaseOrderItem;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class OpenPurchaseOrdersReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $rows = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->whereIn('purchase_orders.status', ['sent', 'partially_received'])
            ->whereRaw('purchase_order_items.received_quantity < purchase_order_items.quantity')
            ->when($filters->supplierId, fn ($q) => $q->where('purchase_orders.supplier_id', $filters->supplierId))
            ->select([
                'purchase_orders.po_number',
                'purchase_orders.order_date',
                'purchase_orders.status as po_status',
                'suppliers.name as supplier_name',
                'products.part_number',
                'products.name as product_name',
                'products.supplier_sell_as',
                'products.units_per_supplier_unit',
                'purchase_order_items.quantity',
                'purchase_order_items.received_quantity',
            ])
            ->orderBy('purchase_orders.order_date')
            ->limit(100)
            ->get()
            ->map(function ($row) {
                $product = new \App\Models\Product([
                    'supplier_sell_as' => $row->supplier_sell_as,
                    'units_per_supplier_unit' => $row->units_per_supplier_unit,
                ]);
                $remaining = max(0, (float) $row->quantity - (float) $row->received_quantity);
                $row->remaining_catalog = CatalogQuantity::orderQuantityFromStock($product, $remaining);

                return $row;
            });

        return [
            'summary' => ['open_lines' => $rows->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'PO' => $r->po_number,
                'Supplier' => $r->supplier_name,
                'Part' => $r->part_number,
                'Ordered (catalog)' => CatalogQuantity::orderQuantityFromStock(
                    new \App\Models\Product(['supplier_sell_as' => $r->supplier_sell_as, 'units_per_supplier_unit' => $r->units_per_supplier_unit]),
                    (float) $r->quantity
                ),
                'Remaining (catalog)' => $r->remaining_catalog,
                'PO Status' => $r->po_status,
                'Order Date' => $r->order_date,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['PO', 'Supplier', 'Part', 'Ordered (catalog)', 'Remaining (catalog)', 'PO Status', 'Order Date'];
    }
}

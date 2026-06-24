<?php

namespace App\Services\Reports;

use App\Models\ReturnItem;
use App\Models\ReturnRecord;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class SupplierReturnsReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = ReturnRecord::query()
            ->where('type', 'supplier')
            ->where('status', 'completed')
            ->where(function ($q) use ($filters) {
                $q->whereBetween('completed_at', [$filters->from, $filters->to])
                    ->orWhere(fn ($inner) => $inner->whereNull('completed_at')->whereBetween('updated_at', [$filters->from, $filters->to]));
            })
            ->when($filters->warehouseId, fn ($q) => $q->where('warehouse_id', $filters->warehouseId));

        $rows = (clone $query)
            ->with(['supplier:id,name', 'warehouse:id,name', 'items.product'])
            ->latest('completed_at')
            ->limit(50)
            ->get();

        return [
            'summary' => ['returns' => (clone $query)->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            ReturnItem::query()
                ->whereHas('returnRecord', function ($q) use ($filters) {
                    $q->where('type', 'supplier')->where('status', 'completed')
                        ->where(function ($inner) use ($filters) {
                            $inner->whereBetween('completed_at', [$filters->from, $filters->to])
                                ->orWhere(fn ($i) => $i->whereNull('completed_at')->whereBetween('updated_at', [$filters->from, $filters->to]));
                        })
                        ->when($filters->warehouseId, fn ($s) => $s->where('warehouse_id', $filters->warehouseId));
                })
                ->with(['returnRecord.supplier', 'returnRecord.warehouse', 'product'])
                ->get()
                ->map(fn (ReturnItem $item) => [
                    'Return' => $item->returnRecord?->return_number,
                    'Supplier' => $item->returnRecord?->supplier?->name,
                    'Warehouse' => $item->returnRecord?->warehouse?->name,
                    'Part' => $item->product?->part_number,
                    'Qty (catalog)' => $item->product ? CatalogQuantity::orderQuantityFromStock($item->product, (float) $item->quantity) : $item->quantity,
                    'Condition' => $item->condition,
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Return', 'Supplier', 'Warehouse', 'Part', 'Qty (catalog)', 'Condition'];
    }
}

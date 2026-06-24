<?php

namespace App\Services\Reports;

use App\Models\StockAdjustmentItem;
use App\Models\Shop;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class StockAdjustmentsReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = $this->baseQuery($filters);

        $rows = (clone $query)
            ->with(['adjustment.location', 'adjustment.approver:id,name', 'product'])
            ->latest('id')
            ->limit(50)
            ->get();

        return [
            'summary' => ['lines' => (clone $query)->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->baseQuery($filters)
                ->with(['adjustment.location', 'adjustment.approver:id,name', 'product'])
                ->get()
                ->map(fn (StockAdjustmentItem $item) => [
                    'Adjustment' => $item->adjustment?->adjustment_number,
                    'Part' => $item->product?->part_number,
                    'Location' => $item->adjustment?->location?->name,
                    'System Qty' => $item->product ? CatalogQuantity::orderQuantityFromStock($item->product, (float) $item->system_quantity) : $item->system_quantity,
                    'Counted Qty' => $item->product ? CatalogQuantity::orderQuantityFromStock($item->product, (float) $item->counted_quantity) : $item->counted_quantity,
                    'Difference' => $item->product ? CatalogQuantity::orderQuantityFromStock($item->product, abs((float) $item->difference)) : $item->difference,
                    'Reason' => $item->adjustment?->reason,
                    'Approved' => $item->adjustment?->approved_at?->format('Y-m-d'),
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Adjustment', 'Part', 'Location', 'System Qty', 'Counted Qty', 'Difference', 'Reason', 'Approved'];
    }

    private function baseQuery(ReportFilters $filters)
    {
        return StockAdjustmentItem::query()
            ->whereHas('adjustment', function ($q) use ($filters) {
                $q->where('status', 'approved')
                    ->whereBetween('approved_at', [$filters->from, $filters->to])
                    ->when($filters->shopId, fn ($inner) => $inner->where('location_type', Shop::class)->where('location_id', $filters->shopId))
                    ->when($filters->warehouseId, fn ($inner) => $inner->where('location_type', \App\Models\Warehouse::class)->where('location_id', $filters->warehouseId));
            });
    }
}

<?php

namespace App\Services\Reports;

use App\Models\ReturnItem;
use App\Models\ReturnRecord;
use App\Models\Shop;
use Illuminate\Support\Collection;

class CustomerReturnsReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = ReturnRecord::query()
            ->where('type', 'customer')
            ->where('status', 'completed')
            ->where(function ($q) use ($filters) {
                $q->whereBetween('completed_at', [$filters->from, $filters->to])
                    ->orWhere(fn ($inner) => $inner->whereNull('completed_at')->whereBetween('updated_at', [$filters->from, $filters->to]));
            })
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));

        $rows = (clone $query)
            ->with(['shop:id,name', 'items.product'])
            ->latest('completed_at')
            ->limit(50)
            ->get();

        $topReasons = (clone $query)
            ->selectRaw('reason, COUNT(*) as count, SUM(refund_amount) as refunds')
            ->groupBy('reason')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'summary' => [
                'returns' => (clone $query)->count(),
                'refunds' => (float) (clone $query)->sum('refund_amount'),
            ],
            'rows' => $rows,
            'topReasons' => $topReasons,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            ReturnItem::query()
                ->whereHas('returnRecord', function ($q) use ($filters) {
                    $q->where('type', 'customer')->where('status', 'completed')
                        ->where(function ($inner) use ($filters) {
                            $inner->whereBetween('completed_at', [$filters->from, $filters->to])
                                ->orWhere(fn ($i) => $i->whereNull('completed_at')->whereBetween('updated_at', [$filters->from, $filters->to]));
                        })
                        ->when($filters->shopId, fn ($s) => $s->where('shop_id', $filters->shopId));
                })
                ->with(['returnRecord.shop', 'product.vehicleMake', 'product.vehicleModel'])
                ->get()
                ->map(fn (ReturnItem $item) => [
                    'Return' => $item->returnRecord?->return_number,
                    'Shop' => $item->returnRecord?->shop?->name,
                    'Part' => $item->product?->part_number,
                    'Fitment' => $item->product?->fitmentLabel(),
                    'Qty' => $item->quantity,
                    'Refund' => $item->lineRefund(),
                    'Reason' => $item->returnRecord?->reason,
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Return', 'Shop', 'Part', 'Fitment', 'Qty', 'Refund', 'Reason'];
    }
}

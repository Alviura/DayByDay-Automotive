<?php

namespace App\Services\Reports;

use App\Models\Sale;
use Illuminate\Support\Collection;

class ReversedSalesReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = Sale::query()
            ->where('status', 'reversed')
            ->where(function ($q) use ($filters) {
                $q->whereBetween('reversed_at', [$filters->from, $filters->to])
                    ->orWhereBetween('updated_at', [$filters->from, $filters->to]);
            })
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));

        $rows = (clone $query)
            ->with(['shop:id,name', 'cashier:id,name', 'reversedBy:id,name'])
            ->latest('reversed_at')
            ->limit(50)
            ->get();

        return [
            'summary' => ['count' => (clone $query)->count(), 'value' => (float) (clone $query)->sum('total')],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            Sale::query()
                ->where('status', 'reversed')
                ->where(function ($q) use ($filters) {
                    $q->whereBetween('reversed_at', [$filters->from, $filters->to])
                        ->orWhereBetween('updated_at', [$filters->from, $filters->to]);
                })
                ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId))
                ->with(['shop:id,name', 'reversedBy:id,name'])
                ->orderByDesc('reversed_at')
                ->get()
                ->map(fn (Sale $s) => [
                    'Receipt' => $s->receipt_number,
                    'Shop' => $s->shop?->name,
                    'Total' => $s->total,
                    'Reversed At' => ($s->reversed_at ?? $s->updated_at)?->format('Y-m-d H:i'),
                    'Reversed By' => $s->reversedBy?->name,
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Receipt', 'Shop', 'Total', 'Reversed At', 'Reversed By'];
    }
}

<?php

namespace App\Services\Reports;

use App\Models\Sale;
use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class HeldOrdersReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $query = Sale::query()
            ->where('status', 'held')
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));

        $rows = (clone $query)
            ->with(['shop:id,name', 'cashier:id,name'])
            ->orderBy('updated_at')
            ->limit(50)
            ->get();

        return [
            'summary' => [
                'count' => (clone $query)->count(),
                'value' => (float) (clone $query)->sum('total'),
            ],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            Sale::query()
                ->where('status', 'held')
                ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId))
                ->with(['shop:id,name', 'cashier:id,name'])
                ->orderBy('updated_at')
                ->get()
                ->map(fn (Sale $s) => [
                    'Receipt' => $s->receipt_number,
                    'Shop' => $s->shop?->name,
                    'Type' => $s->saleTypeLabel(),
                    'Total' => $s->total,
                    'Held Since' => $s->updated_at?->format('Y-m-d H:i'),
                    'Cashier' => $s->cashier?->name,
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Receipt', 'Shop', 'Type', 'Total', 'Held Since', 'Cashier'];
    }
}

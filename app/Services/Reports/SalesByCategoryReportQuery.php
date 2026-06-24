<?php

namespace App\Services\Reports;

use App\Models\SaleItem;
use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class SalesByCategoryReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->selectRaw('COALESCE(categories.name, \'Uncategorised\') as category_name, COUNT(DISTINCT sale_items.id) as lines, SUM(sale_items.quantity) as qty, SUM(sale_items.line_total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return [
            'summary' => ['categories' => $rows->count(), 'revenue' => (float) $rows->sum('revenue')],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'Category' => $r->category_name,
                'Lines' => $r->lines,
                'Qty' => $r->qty,
                'Revenue' => $r->revenue,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Category', 'Lines', 'Qty', 'Revenue'];
    }
}

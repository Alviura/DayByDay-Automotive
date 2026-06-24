<?php

namespace App\Services\Reports;

use App\Models\SaleItem;
use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class GrossMarginReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->whereNotNull('sale_items.unit_cost')
            ->selectRaw('products.part_number, products.name,
                SUM(sale_items.quantity) as qty,
                SUM(sale_items.line_total) as revenue,
                SUM(sale_items.quantity * sale_items.unit_cost) as cogs')
            ->groupBy('products.id', 'products.part_number', 'products.name')
            ->orderByDesc('revenue')
            ->limit(30)
            ->get()
            ->map(function ($row) {
                $row->margin = (float) $row->revenue - (float) $row->cogs;
                $row->margin_pct = $row->revenue > 0 ? round($row->margin / $row->revenue * 100, 1) : 0;

                return $row;
            });

        $totals = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->whereNotNull('sale_items.unit_cost')
            ->selectRaw('SUM(sale_items.line_total) as revenue, SUM(sale_items.quantity * sale_items.unit_cost) as cogs')
            ->first();

        $revenue = (float) ($totals->revenue ?? 0);
        $cogs = (float) ($totals->cogs ?? 0);

        return [
            'summary' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'margin' => $revenue - $cogs,
                'margin_pct' => $revenue > 0 ? round(($revenue - $cogs) / $revenue * 100, 1) : 0,
                'lines_with_cost' => $rows->count(),
            ],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            SaleItem::query()
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.status', 'completed')
                ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
                ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
                ->whereNotNull('sale_items.unit_cost')
                ->selectRaw('products.part_number, products.name, sale_items.quantity, sale_items.unit_price, sale_items.unit_cost, sale_items.line_total,
                    (sale_items.quantity * sale_items.unit_cost) as cogs, sales.receipt_number, sales.sold_at')
                ->orderBy('sales.sold_at')
                ->get()
                ->map(fn ($r) => [
                    'Receipt' => $r->receipt_number,
                    'Part' => $r->part_number,
                    'Qty' => $r->quantity,
                    'Revenue' => $r->line_total,
                    'COGS' => $r->cogs,
                    'Margin' => $r->line_total - $r->cogs,
                    'Sold At' => $r->sold_at,
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Receipt', 'Part', 'Qty', 'Revenue', 'COGS', 'Margin', 'Sold At'];
    }
}

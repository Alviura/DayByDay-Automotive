<?php

namespace App\Services\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $salesQuery = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));

        $summary = [
            'transaction_count' => (clone $salesQuery)->count(),
            'gross_revenue' => (float) (clone $salesQuery)->sum('total'),
            'tax_collected' => (float) (clone $salesQuery)->sum('tax_total'),
            'avg_ticket' => 0,
        ];

        if ($summary['transaction_count'] > 0) {
            $summary['avg_ticket'] = round($summary['gross_revenue'] / $summary['transaction_count'], 2);
        }

        $daily = (clone $salesQuery)
            ->selectRaw('DATE(sold_at) as sale_date, COUNT(*) as transactions, SUM(total) as revenue')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        $topProducts = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->groupBy('sale_items.product_id', 'products.part_number', 'products.name')
            ->selectRaw('products.part_number, products.name, SUM(sale_items.quantity) as qty_sold, SUM(sale_items.line_total) as revenue')
            ->orderByDesc('revenue')
            ->limit(15)
            ->get();

        $recent = (clone $salesQuery)
            ->with(['shop:id,name', 'cashier:id,name'])
            ->latest('sold_at')
            ->limit(20)
            ->get(['id', 'receipt_number', 'shop_id', 'user_id', 'total', 'sold_at']);

        return compact('summary', 'daily', 'topProducts', 'recent');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $data = $this->run($filters);

        return $data['recent']->map(fn (Sale $sale) => [
            'Receipt' => $sale->receipt_number,
            'Shop' => $sale->shop?->name,
            'Cashier' => $sale->cashier?->name,
            'Total' => $sale->total,
            'Sold At' => $sale->sold_at?->format('Y-m-d H:i'),
        ]);
    }
}

<?php

namespace App\Services\Reports;

use App\Contracts\ReportQuery;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

/**
 * Data contract §4.1 — completed sales by sold_at; revenue from sales.total.
 */
class SalesReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $salesQuery = $this->completedSalesQuery($filters);

        $summary = [
            'transaction_count' => (clone $salesQuery)->count(),
            'gross_revenue' => (float) (clone $salesQuery)->sum('total'),
            'tax_collected' => (float) (clone $salesQuery)->sum('tax_total'),
            'avg_ticket' => 0,
            'retail_count' => (clone $salesQuery)->where('sale_type', 'retail')->count(),
            'credit_count' => (clone $salesQuery)->where('sale_type', 'credit')->count(),
            'reinstatement_count' => (clone $salesQuery)->where('sale_type', 'reinstatement')->count(),
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
            ->leftJoin('vehicle_makes', 'products.vehicle_make_id', '=', 'vehicle_makes.id')
            ->leftJoin('vehicle_models', 'products.vehicle_model_id', '=', 'vehicle_models.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->groupBy(
                'sale_items.product_id',
                'products.part_number',
                'products.name',
                'vehicle_makes.name',
                'vehicle_models.name'
            )
            ->selectRaw(
                'products.part_number, products.name,
                vehicle_makes.name as make_name, vehicle_models.name as model_name,
                SUM(sale_items.quantity) as qty_sold, SUM(sale_items.line_total) as revenue'
            )
            ->orderByDesc('revenue')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $row->fitment = trim(($row->make_name ?? '').' '.($row->model_name ?? '')) ?: 'Universal';

                return $row;
            });

        $recent = (clone $salesQuery)
            ->with(['shop:id,name', 'cashier:id,name'])
            ->latest('sold_at')
            ->limit(20)
            ->get(['id', 'receipt_number', 'shop_id', 'user_id', 'sale_type', 'total', 'sold_at']);

        return compact('summary', 'daily', 'topProducts', 'recent');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->completedSalesQuery($filters)
                ->with(['shop:id,name', 'cashier:id,name'])
                ->orderBy('sold_at')
                ->get()
                ->map(fn (Sale $sale) => [
                    'Receipt' => $sale->receipt_number,
                    'Shop' => $sale->shop?->name,
                    'Type' => $sale->saleTypeLabel(),
                    'Cashier' => $sale->cashier?->name,
                    'Subtotal' => $sale->subtotal,
                    'Tax' => $sale->tax_total,
                    'Total' => $sale->total,
                    'Sold At' => $sale->sold_at?->format('Y-m-d H:i'),
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Receipt', 'Shop', 'Type', 'Cashier', 'Subtotal', 'Tax', 'Total', 'Sold At'];
    }
}

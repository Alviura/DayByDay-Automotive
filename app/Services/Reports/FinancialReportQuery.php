<?php

namespace App\Services\Reports;

use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Services\InventoryService;
use Illuminate\Support\Collection;

class FinancialReportQuery
{
    public function __construct(private InventoryService $inventory) {}

    public function run(ReportFilters $filters): array
    {
        $salesQuery = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));

        $revenue = (float) (clone $salesQuery)->sum('total');
        $discounts = (float) (clone $salesQuery)->sum('discount_total');
        $tax = (float) (clone $salesQuery)->sum('tax_total');

        $refundsQuery = ReturnRecord::query()
            ->where('type', 'customer')
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));

        $refunds = (float) (clone $refundsQuery)->sum('refund_amount');

        $inventoryValue = 0;
        if ($filters->shopId) {
            $shop = Shop::find($filters->shopId);
            if ($shop) {
                $inventoryValue = $this->inventory->valuation($shop)['total_value'];
            }
        } else {
            $inventoryValue = (float) StockBalance::selectRaw('SUM(quantity_on_hand * average_cost) as total')->value('total');
        }

        $netRevenue = $revenue - $refunds;

        $summary = [
            'gross_revenue' => $revenue,
            'discounts' => $discounts,
            'tax_collected' => $tax,
            'refunds' => $refunds,
            'net_revenue' => $netRevenue,
            'inventory_value' => $inventoryValue,
            'transaction_count' => (clone $salesQuery)->count(),
        ];

        $paymentBreakdown = \App\Models\Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->selectRaw('payments.method, SUM(payments.amount) as total')
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get();

        return compact('summary', 'paymentBreakdown');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $data = $this->run($filters);

        return collect([
            ['Metric' => 'Gross Revenue', 'Amount' => $data['summary']['gross_revenue']],
            ['Metric' => 'Discounts', 'Amount' => $data['summary']['discounts']],
            ['Metric' => 'Tax Collected', 'Amount' => $data['summary']['tax_collected']],
            ['Metric' => 'Refunds', 'Amount' => $data['summary']['refunds']],
            ['Metric' => 'Net Revenue', 'Amount' => $data['summary']['net_revenue']],
            ['Metric' => 'Inventory Value (current)', 'Amount' => $data['summary']['inventory_value']],
        ]);
    }
}

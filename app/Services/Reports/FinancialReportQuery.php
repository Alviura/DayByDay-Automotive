<?php

namespace App\Services\Reports;

use App\Models\ReturnRecord;
use App\Models\Shop;
use App\Models\StockBalance;
use App\Services\InventoryService;
use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

/** Data contract §4.1 + §4.5 — sales by sold_at; refunds by returns.completed_at. */
class FinancialReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function __construct(private InventoryService $inventory) {}

    public function run(ReportFilters $filters): array
    {
        $salesQuery = $this->completedSalesQuery($filters);

        $revenue = (float) (clone $salesQuery)->sum('total');
        $tax = (float) (clone $salesQuery)->sum('tax_total');

        $refundsQuery = ReturnRecord::query()
            ->where('type', 'customer')
            ->where('status', 'completed')
            ->where(function ($q) use ($filters) {
                $q->whereBetween('completed_at', [$filters->from, $filters->to])
                    ->orWhere(function ($inner) use ($filters) {
                        $inner->whereNull('completed_at')
                            ->whereBetween('updated_at', [$filters->from, $filters->to]);
                    });
            })
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

        $summary = [
            'gross_revenue' => $revenue,
            'tax_collected' => $tax,
            'refunds' => $refunds,
            'net_revenue' => $revenue - $refunds,
            'inventory_value' => $inventoryValue,
            'transaction_count' => (clone $salesQuery)->count(),
        ];

        $paymentBreakdown = \App\Models\Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->selectRaw('payments.method, SUM(payments.amount) as total, COUNT(*) as payments')
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get();

        $saleTypeBreakdown = (clone $salesQuery)
            ->selectRaw('sale_type, COUNT(*) as tickets, SUM(total) as revenue')
            ->groupBy('sale_type')
            ->get();

        return compact('summary', 'paymentBreakdown', 'saleTypeBreakdown');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $data = $this->run($filters);
        $rows = collect([
            ['Metric' => 'Gross Revenue', 'Amount' => $data['summary']['gross_revenue']],
            ['Metric' => 'Tax Collected', 'Amount' => $data['summary']['tax_collected']],
            ['Metric' => 'Refunds', 'Amount' => $data['summary']['refunds']],
            ['Metric' => 'Net Revenue', 'Amount' => $data['summary']['net_revenue']],
            ['Metric' => 'Inventory Value (current)', 'Amount' => $data['summary']['inventory_value']],
        ]);

        foreach ($data['paymentBreakdown'] as $payment) {
            $rows->push([
                'Metric' => 'Payment: '.$payment->method,
                'Amount' => $payment->total,
            ]);
        }

        return $rows;
    }

    public function csvHeaders(): array
    {
        return ['Metric', 'Amount'];
    }
}

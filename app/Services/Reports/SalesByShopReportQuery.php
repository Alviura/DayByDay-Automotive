<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class SalesByShopReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = $this->completedSalesQuery($filters)
            ->join('shops', 'sales.shop_id', '=', 'shops.id')
            ->selectRaw('shops.id, shops.name, shops.code, COUNT(sales.id) as transactions, SUM(sales.total) as revenue, AVG(sales.total) as avg_ticket')
            ->groupBy('shops.id', 'shops.name', 'shops.code')
            ->orderByDesc('revenue')
            ->get();

        return [
            'summary' => [
                'shops' => $rows->count(),
                'revenue' => (float) $rows->sum('revenue'),
                'transactions' => (int) $rows->sum('transactions'),
            ],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'Shop' => $r->name,
                'Code' => $r->code,
                'Transactions' => $r->transactions,
                'Revenue' => $r->revenue,
                'Avg Ticket' => round($r->avg_ticket, 2),
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Shop', 'Code', 'Transactions', 'Revenue', 'Avg Ticket'];
    }
}

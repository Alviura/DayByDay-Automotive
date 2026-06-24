<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class SalesByCashierReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = $this->completedSalesQuery($filters)
            ->join('users', 'sales.completed_by', '=', 'users.id')
            ->selectRaw('users.id, users.name, COUNT(sales.id) as transactions, SUM(sales.total) as revenue, AVG(sales.total) as avg_ticket')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->get();

        return [
            'summary' => [
                'cashiers' => $rows->count(),
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
                'Cashier' => $r->name,
                'Transactions' => $r->transactions,
                'Revenue' => $r->revenue,
                'Avg Ticket' => round($r->avg_ticket, 2),
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Cashier', 'Transactions', 'Revenue', 'Avg Ticket'];
    }
}

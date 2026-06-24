<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class OnAccountVsCashReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = $this->completedSalesQuery($filters)
            ->selectRaw("sale_type, COUNT(*) as tickets, SUM(total) as revenue")
            ->groupBy('sale_type')
            ->get()
            ->map(function ($row) {
                $labels = ['retail' => 'Retail (cash)', 'credit' => 'Fleet (on account)', 'reinstatement' => 'Reinstatement (on account)'];
                $row->type_label = $labels[$row->sale_type] ?? ucfirst($row->sale_type);

                return $row;
            });

        return [
            'summary' => ['tickets' => (int) $rows->sum('tickets'), 'revenue' => (float) $rows->sum('revenue')],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'Sale Type' => $r->type_label,
                'Tickets' => $r->tickets,
                'Revenue' => $r->revenue,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Sale Type', 'Tickets', 'Revenue'];
    }
}

<?php

namespace App\Services\Reports;

use App\Models\StockLedger;
use App\Services\Reports\Concerns\ScopesSales;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class StockMovementsReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = $this->applyLocationScope(
            StockLedger::query()->whereBetween('created_at', [$filters->from, $filters->to])
        )
            ->selectRaw('transaction_type, COUNT(*) as entries, SUM(ABS(quantity)) as stock_qty')
            ->groupBy('transaction_type')
            ->orderBy('transaction_type')
            ->get()
            ->map(function ($row) {
                $row->type_label = StockLedger::TYPE_LABELS[$row->transaction_type] ?? $row->transaction_type;

                return $row;
            });

        return [
            'summary' => ['entries' => (int) $rows->sum('entries'), 'stock_qty' => (float) $rows->sum('stock_qty')],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'Type' => $r->type_label,
                'Entries' => $r->entries,
                'Stock Qty' => $r->stock_qty,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Type', 'Entries', 'Stock Qty'];
    }
}

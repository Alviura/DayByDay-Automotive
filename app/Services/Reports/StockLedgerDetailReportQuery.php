<?php

namespace App\Services\Reports;

use App\Models\StockLedger;
use App\Services\Reports\Concerns\ScopesSales;
use App\Support\CatalogQuantity;
use Illuminate\Support\Collection;

class StockLedgerDetailReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $query = $this->applyLocationScope(
            StockLedger::query()
                ->whereBetween('created_at', [$filters->from, $filters->to])
                ->with(['product.vehicleMake', 'product.vehicleModel', 'location', 'user:id,name'])
        );

        $rows = (clone $query)->latest()->limit(100)->get();

        return [
            'summary' => ['entries' => (clone $query)->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->applyLocationScope(
                StockLedger::query()
                    ->whereBetween('created_at', [$filters->from, $filters->to])
                    ->with(['product.vehicleMake', 'product.vehicleModel', 'location', 'user:id,name'])
                    ->orderBy('created_at')
                    ->get()
            )->map(fn (StockLedger $m) => [
                'Date' => $m->created_at?->format('Y-m-d H:i'),
                'Part' => $m->product?->part_number,
                'Product' => $m->product?->name,
                'Fitment' => $m->product?->fitmentLabel(),
                'Location' => $m->location?->name,
                'Type' => $m->transactionLabel(),
                'Qty (catalog)' => $m->product ? CatalogQuantity::orderQuantityFromStock($m->product, abs((float) $m->quantity)) : abs($m->quantity),
                'Stock Pcs' => $m->quantity,
                'Balance' => $m->balance_after,
                'Reference' => $m->reference_number,
                'User' => $m->user?->name,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Date', 'Part', 'Product', 'Fitment', 'Location', 'Type', 'Qty (catalog)', 'Stock Pcs', 'Balance', 'Reference', 'User'];
    }
}

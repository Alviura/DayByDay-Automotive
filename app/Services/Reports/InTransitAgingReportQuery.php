<?php

namespace App\Services\Reports;

use App\Models\StockTransfer;
use Illuminate\Support\Collection;

class InTransitAgingReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $rows = StockTransfer::query()
            ->whereIn('status', ['dispatched', 'in_transit', 'partially_received'])
            ->whereNotNull('dispatched_at')
            ->with(['source', 'destination'])
            ->orderBy('dispatched_at')
            ->get()
            ->map(function (StockTransfer $t) {
                $t->days_in_transit = (int) $t->dispatched_at?->diffInDays(now());

                return $t;
            });

        return [
            'summary' => [
                'in_transit' => $rows->count(),
                'avg_days' => $rows->count() > 0 ? round($rows->avg('days_in_transit'), 1) : 0,
                'over_7_days' => $rows->where('days_in_transit', '>', 7)->count(),
            ],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn (StockTransfer $t) => [
                'Transfer' => $t->transfer_number,
                'Route' => $t->routeLabel(),
                'Status' => $t->statusLabel(),
                'Dispatched' => $t->dispatched_at?->format('Y-m-d'),
                'Days In Transit' => $t->days_in_transit,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Transfer', 'Route', 'Status', 'Dispatched', 'Days In Transit'];
    }
}

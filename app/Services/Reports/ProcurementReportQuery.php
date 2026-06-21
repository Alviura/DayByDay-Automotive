<?php

namespace App\Services\Reports;

use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\QuotationSeries;
use Illuminate\Support\Collection;

class ProcurementReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $seriesQuery = QuotationSeries::query()
            ->whereBetween('created_at', [$filters->from, $filters->to]);

        $statusBreakdown = (clone $seriesQuery)
            ->selectRaw('status, COUNT(*) as count, SUM(COALESCE(total_actual_cost, total_landing_cost)) as value')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $poQuery = PurchaseOrder::query()
            ->whereBetween('order_date', [$filters->from->toDateString(), $filters->to->toDateString()]);

        $grnQuery = GoodsReceiptNote::query()
            ->whereBetween('received_at', [$filters->from, $filters->to]);

        $summary = [
            'series_open' => QuotationSeries::whereNotIn('status', ['closed', 'cancelled'])->count(),
            'series_in_period' => (clone $seriesQuery)->count(),
            'po_value' => (float) (clone $poQuery)->sum('total'),
            'po_count' => (clone $poQuery)->count(),
            'grn_count' => (clone $grnQuery)->count(),
        ];

        $recentSeries = (clone $seriesQuery)
            ->with('supplier:id,name')
            ->latest()
            ->limit(15)
            ->get(['id', 'series_number', 'title', 'supplier_id', 'status', 'total_actual_cost', 'total_landing_cost', 'currency', 'created_at']);

        $recentPos = (clone $poQuery)
            ->with('supplier:id,name')
            ->latest()
            ->limit(15)
            ->get(['id', 'po_number', 'supplier_id', 'status', 'total', 'currency', 'order_date']);

        return compact('summary', 'statusBreakdown', 'recentSeries', 'recentPos');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $data = $this->run($filters);

        return $data['recentSeries']->map(fn ($series) => [
            'Series' => $series->displayName(),
            'Reference' => $series->series_number,
            'Supplier' => $series->supplier?->name,
            'Status' => $series->statusLabel(),
            'Actual Cost' => $series->total_actual_cost ?: $series->total_landing_cost,
            'Currency' => $series->currency,
            'Created' => $series->created_at?->format('Y-m-d'),
        ]);
    }
}

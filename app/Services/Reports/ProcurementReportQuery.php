<?php

namespace App\Services\Reports;

use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\QuotationSeries;
use Illuminate\Support\Collection;

/** Data contract §4.3 — procurement by created_at / order_date / received_at. */
class ProcurementReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $seriesQuery = QuotationSeries::query()
            ->whereBetween('created_at', [$filters->from, $filters->to])
            ->when($filters->supplierId, fn ($q) => $q->where('supplier_id', $filters->supplierId));

        $statusBreakdown = (clone $seriesQuery)
            ->selectRaw('status, COUNT(*) as count, SUM(COALESCE(total_actual_cost, total_landing_cost)) as value')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $poQuery = PurchaseOrder::query()
            ->whereBetween('order_date', [$filters->from->toDateString(), $filters->to->toDateString()])
            ->when($filters->supplierId, fn ($q) => $q->where('supplier_id', $filters->supplierId));

        $grnQuery = GoodsReceiptNote::query()
            ->whereBetween('received_at', [$filters->from, $filters->to])
            ->where('status', 'posted');

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
            ->get();

        $recentPos = (clone $poQuery)
            ->with('supplier:id,name')
            ->latest()
            ->limit(15)
            ->get();

        return compact('summary', 'statusBreakdown', 'recentSeries', 'recentPos');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            PurchaseOrder::query()
                ->with('supplier:id,name')
                ->whereBetween('order_date', [$filters->from->toDateString(), $filters->to->toDateString()])
                ->when($filters->supplierId, fn ($q) => $q->where('supplier_id', $filters->supplierId))
                ->orderBy('order_date')
                ->get()
                ->map(fn ($po) => [
                    'PO Number' => $po->po_number,
                    'Supplier' => $po->supplier?->name,
                    'Status' => $po->status,
                    'Total' => $po->total,
                    'Currency' => $po->currency,
                    'Order Date' => $po->order_date,
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['PO Number', 'Supplier', 'Status', 'Total', 'Currency', 'Order Date'];
    }
}

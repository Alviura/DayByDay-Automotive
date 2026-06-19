<?php

namespace App\Services\Reports;

use App\Models\GoodsReceiptNote;
use App\Models\ProcurementFolder;
use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;

class ProcurementReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $foldersQuery = ProcurementFolder::query()
            ->whereBetween('created_at', [$filters->from, $filters->to]);

        $statusBreakdown = (clone $foldersQuery)
            ->selectRaw('status, COUNT(*) as count, SUM(total_landing_cost) as value')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $poQuery = PurchaseOrder::query()
            ->whereBetween('order_date', [$filters->from->toDateString(), $filters->to->toDateString()]);

        $grnQuery = GoodsReceiptNote::query()
            ->whereBetween('received_at', [$filters->from, $filters->to]);

        $summary = [
            'folders_open' => ProcurementFolder::whereNotIn('status', ['closed', 'cancelled'])->count(),
            'folders_in_period' => (clone $foldersQuery)->count(),
            'po_value' => (float) (clone $poQuery)->sum('total'),
            'po_count' => (clone $poQuery)->count(),
            'grn_count' => (clone $grnQuery)->count(),
        ];

        $recentFolders = (clone $foldersQuery)
            ->with('supplier:id,name')
            ->latest()
            ->limit(15)
            ->get(['id', 'folder_number', 'supplier_id', 'status', 'total_landing_cost', 'currency', 'created_at']);

        $recentPos = (clone $poQuery)
            ->with('supplier:id,name')
            ->latest()
            ->limit(15)
            ->get(['id', 'po_number', 'supplier_id', 'status', 'total', 'currency', 'order_date']);

        return compact('summary', 'statusBreakdown', 'recentFolders', 'recentPos');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $data = $this->run($filters);

        return $data['recentFolders']->map(fn ($folder) => [
            'Folder' => $folder->folder_number,
            'Supplier' => $folder->supplier?->name,
            'Status' => $folder->statusLabel(),
            'Landing Cost' => $folder->total_landing_cost,
            'Currency' => $folder->currency,
            'Created' => $folder->created_at?->format('Y-m-d'),
        ]);
    }
}

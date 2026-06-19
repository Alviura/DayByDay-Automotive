<?php

namespace App\Services\Reports;

use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\TransferRequest;
use Illuminate\Support\Collection;

class TransferReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $requestQuery = TransferRequest::query()
            ->whereBetween('created_at', [$filters->from, $filters->to])
            ->when($filters->shopId, function ($q) use ($filters) {
                $q->where(function ($inner) use ($filters) {
                    $inner->where(function ($q) use ($filters) {
                        $q->where('destination_type', Shop::class)->where('destination_id', $filters->shopId);
                    })->orWhere(function ($q) use ($filters) {
                        $q->where('source_type', Shop::class)->where('source_id', $filters->shopId);
                    });
                });
            });

        $statusBreakdown = (clone $requestQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $transferQuery = StockTransfer::query()
            ->whereBetween('dispatched_at', [$filters->from, $filters->to]);

        $summary = [
            'requests_in_period' => (clone $requestQuery)->count(),
            'pending' => TransferRequest::where('status', 'pending')->count(),
            'in_transit' => TransferRequest::where('status', 'dispatched')->count(),
            'dispatched_in_period' => (clone $transferQuery)->count(),
            'completed_in_period' => (clone $requestQuery)->where('status', 'completed')->count(),
        ];

        $recentRequests = (clone $requestQuery)
            ->with(['source', 'destination'])
            ->latest()
            ->limit(15)
            ->get();

        return compact('summary', 'statusBreakdown', 'recentRequests');
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        $data = $this->run($filters);

        return $data['recentRequests']->map(fn (TransferRequest $request) => [
            'Request' => $request->request_number,
            'Route' => $request->routeLabel(),
            'Type' => $request->typeLabel(),
            'Status' => $request->statusLabel(),
            'Created' => $request->created_at?->format('Y-m-d'),
        ]);
    }
}

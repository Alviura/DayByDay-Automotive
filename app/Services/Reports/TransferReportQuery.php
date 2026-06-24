<?php

namespace App\Services\Reports;

use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\TransferRequest;
use Illuminate\Support\Collection;

/** Data contract §4.4 — requests by created_at; transfers by dispatched_at. */
class TransferReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $requestQuery = TransferRequest::query()
            ->whereBetween('created_at', [$filters->from, $filters->to])
            ->when($filters->shopId, function ($q) use ($filters) {
                $q->where(function ($inner) use ($filters) {
                    $inner->where(fn ($q) => $q->where('destination_type', Shop::class)->where('destination_id', $filters->shopId))
                        ->orWhere(fn ($q) => $q->where('source_type', Shop::class)->where('source_id', $filters->shopId));
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
            'pending' => TransferRequest::where('status', 'submitted')->count(),
            'in_transit' => StockTransfer::whereIn('status', ['dispatched', 'in_transit', 'partially_received'])->count(),
            'dispatched_in_period' => (clone $transferQuery)->count(),
            'completed_in_period' => (clone $requestQuery)->where('status', 'fulfilled')->count(),
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
        return $this->truncateIfNeeded(
            TransferRequest::query()
                ->with(['source', 'destination'])
                ->whereBetween('created_at', [$filters->from, $filters->to])
                ->when($filters->shopId, function ($q) use ($filters) {
                    $q->where(function ($inner) use ($filters) {
                        $inner->where(fn ($q) => $q->where('destination_type', Shop::class)->where('destination_id', $filters->shopId))
                            ->orWhere(fn ($q) => $q->where('source_type', Shop::class)->where('source_id', $filters->shopId));
                    });
                })
                ->orderBy('created_at')
                ->get()
                ->map(fn (TransferRequest $r) => [
                    'Request' => $r->request_number,
                    'Route' => $r->routeLabel(),
                    'Type' => $r->typeLabel(),
                    'Status' => $r->statusLabel(),
                    'Created' => $r->created_at?->format('Y-m-d H:i'),
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Request', 'Route', 'Type', 'Status', 'Created'];
    }
}

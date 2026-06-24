<?php

namespace App\Services\Reports;

use App\Models\Shop;
use App\Models\TransferRequest;
use Illuminate\Support\Collection;

class TransferFillRateReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = TransferRequest::query()
            ->whereBetween('created_at', [$filters->from, $filters->to])
            ->when($filters->shopId, function ($q) use ($filters) {
                $q->where(fn ($inner) => $inner->where('destination_type', Shop::class)->where('destination_id', $filters->shopId));
            });

        $total = (clone $query)->count();
        $accepted = (clone $query)->whereIn('status', ['accepted', 'fulfilled'])->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();
        $fulfilled = (clone $query)->where('status', 'fulfilled')->count();

        $rows = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return [
            'summary' => [
                'total' => $total,
                'acceptance_rate' => $total > 0 ? round($accepted / $total * 100, 1) : 0,
                'fulfilment_rate' => $total > 0 ? round($fulfilled / $total * 100, 1) : 0,
                'rejected' => $rejected,
            ],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            TransferRequest::query()
                ->whereBetween('created_at', [$filters->from, $filters->to])
                ->when($filters->shopId, fn ($q) => $q->where('destination_type', Shop::class)->where('destination_id', $filters->shopId))
                ->with(['source', 'destination'])
                ->orderBy('created_at')
                ->get()
                ->map(fn (TransferRequest $r) => [
                    'Request' => $r->request_number,
                    'Route' => $r->routeLabel(),
                    'Status' => $r->statusLabel(),
                    'Created' => $r->created_at?->format('Y-m-d'),
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Request', 'Route', 'Status', 'Created'];
    }
}

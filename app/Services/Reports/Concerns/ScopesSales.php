<?php

namespace App\Services\Reports\Concerns;

use App\Models\Sale;
use App\Models\Shop;
use App\Services\Reports\ReportFilters;
use Illuminate\Database\Eloquent\Builder;

trait ScopesSales
{
    protected function completedSalesQuery(ReportFilters $filters): Builder
    {
        return Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('shop_id', $filters->shopId));
    }

    protected function applyLocationScope(Builder $query, ReportFilters $filters, string $locationTypeColumn = 'location_type', string $locationIdColumn = 'location_id'): Builder
    {
        if ($filters->shopId) {
            return $query
                ->where($locationTypeColumn, Shop::class)
                ->where($locationIdColumn, $filters->shopId);
        }

        if ($filters->warehouseId) {
            return $query
                ->where($locationTypeColumn, \App\Models\Warehouse::class)
                ->where($locationIdColumn, $filters->warehouseId);
        }

        return $query;
    }
}

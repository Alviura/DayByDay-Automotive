<?php

namespace App\Services\Reports;

use App\Models\User;
use App\Models\Warehouse;
use App\Support\ReportRegistry;
use Illuminate\Auth\Access\AuthorizationException;

class ReportScopeService
{
    public function scopedShopId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if ($user?->hasRole('Shop Manager') && $user->shop_id) {
            return (int) $user->shop_id;
        }

        return null;
    }

    public function scopedWarehouseId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if ($user?->hasRole('Warehouse Manager') && $user->warehouse_id) {
            return (int) $user->warehouse_id;
        }

        return null;
    }

    public function isAdministrator(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasRole('Administrator') ?? false;
    }

    /**
     * @return list<int>
     */
    public function allowedShopIds(?User $user = null): array
    {
        $scoped = $this->scopedShopId($user);

        if ($scoped) {
            return [$scoped];
        }

        return \App\Models\Shop::active()->pluck('id')->all();
    }

    /**
     * @return list<int>
     */
    public function allowedWarehouseIds(?User $user = null): array
    {
        $scoped = $this->scopedWarehouseId($user);

        if ($scoped) {
            return [$scoped];
        }

        return Warehouse::active()->pluck('id')->all();
    }

    public function assertCanView(User $user, array $definition): void
    {
        if (! $user->can('reports.view')) {
            throw new AuthorizationException('You cannot view reports.');
        }

        $scopes = $definition['scopes'] ?? [];

        if ($this->scopedShopId($user) && in_array('global', $scopes, true) && ! in_array('shop', $scopes, true)) {
            throw new AuthorizationException('This report is not available for your shop scope.');
        }

        if ($this->scopedWarehouseId($user) && ! $this->isAdministrator($user)) {
            $allowed = array_intersect($scopes, ['warehouse', 'global', 'shop']);
            if ($allowed === []) {
                throw new AuthorizationException('This report is not available for warehouse users.');
            }
        }
    }

    public function applyForcedScope(ReportFilters $filters, array $definition): ReportFilters
    {
        $scopes = $definition['scopes'] ?? [];

        if ($scopedShop = $this->scopedShopId()) {
            if (in_array('shop', $scopes, true)) {
                return $filters->withShopId($scopedShop);
            }
        }

        if ($scopedWarehouse = $this->scopedWarehouseId()) {
            if (in_array('warehouse', $scopes, true) && ! $filters->shopId) {
                return $filters->withWarehouseId($scopedWarehouse);
            }
        }

        return $filters;
    }
}

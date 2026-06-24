<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;

class StockTransferAccessService
{
    public function __construct(private WarehouseAccessService $warehouses) {}

    public function scopedShopId(User $user): ?int
    {
        if ($user->hasRole('Shop Manager') && $user->shop_id) {
            return (int) $user->shop_id;
        }

        return null;
    }

    public function scopedWarehouseId(User $user): ?int
    {
        return $this->warehouses->scopedWarehouseId($user);
    }

    public function isAdministrator(User $user): bool
    {
        return $this->warehouses->isAdministrator($user);
    }

    /**
     * @return list<string>
     */
    public function allowedCreateTypes(User $user): array
    {
        if ($this->isAdministrator($user)) {
            return ['warehouse_to_shop', 'inter_shop', 'shop_to_warehouse'];
        }

        if ($this->scopedWarehouseId($user)) {
            return ['warehouse_to_shop', 'shop_to_warehouse'];
        }

        return [];
    }

    public function canCreateType(User $user, string $type): bool
    {
        return in_array($type, $this->allowedCreateTypes($user), true);
    }

    public function canView(User $user, StockTransfer $transfer): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        $shopId = $this->scopedShopId($user);
        if ($shopId && $this->involvesShop($transfer, $shopId)) {
            return true;
        }

        $warehouseId = $this->scopedWarehouseId($user);

        return $warehouseId && $this->involvesWarehouse($transfer, $warehouseId);
    }

    public function canManage(User $user, StockTransfer $transfer): bool
    {
        if (! $user->can('transfers.create')) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        $warehouseId = $this->scopedWarehouseId($user);
        if ($warehouseId) {
            return $transfer->source_type === Warehouse::class
                && (int) $transfer->source_id === $warehouseId;
        }

        return false;
    }

    public function canDispatch(User $user, StockTransfer $transfer): bool
    {
        if (! $transfer->canDispatch()) {
            return false;
        }

        if (! $user->can('transfers.dispatch')) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        $warehouseId = $this->scopedWarehouseId($user);
        if ($warehouseId
            && $transfer->source_type === Warehouse::class
            && (int) $transfer->source_id === $warehouseId) {
            return true;
        }

        $shopId = $this->scopedShopId($user);

        return $shopId
            && $transfer->source_type === Shop::class
            && (int) $transfer->source_id === $shopId;
    }

    public function canReceive(User $user, StockTransfer $transfer): bool
    {
        if (! $user->can('transfers.receive')) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return true;
        }

        $shopId = $this->scopedShopId($user);
        if ($shopId) {
            return $transfer->destination_type === Shop::class
                && (int) $transfer->destination_id === $shopId;
        }

        $warehouseId = $this->scopedWarehouseId($user);

        return $warehouseId
            && $transfer->destination_type === Warehouse::class
            && (int) $transfer->destination_id === $warehouseId;
    }

    public function scopeVisible(Builder $query, User $user): Builder
    {
        if ($this->isAdministrator($user)) {
            return $query;
        }

        $shopId = $this->scopedShopId($user);
        $warehouseId = $this->scopedWarehouseId($user);

        return $query->where(function (Builder $q) use ($shopId, $warehouseId) {
            if ($shopId) {
                $q->where(function (Builder $sub) use ($shopId) {
                    $sub->where('source_type', Shop::class)->where('source_id', $shopId);
                })->orWhere(function (Builder $sub) use ($shopId) {
                    $sub->where('destination_type', Shop::class)->where('destination_id', $shopId);
                });
            }

            if ($warehouseId) {
                $method = $shopId ? 'orWhere' : 'where';
                $q->{$method}(function (Builder $sub) use ($warehouseId) {
                    $sub->where(function (Builder $inner) use ($warehouseId) {
                        $inner->where('source_type', Warehouse::class)->where('source_id', $warehouseId);
                    })->orWhere(function (Builder $inner) use ($warehouseId) {
                        $inner->where('destination_type', Warehouse::class)->where('destination_id', $warehouseId);
                    });
                });
            }
        });
    }

    private function involvesShop(StockTransfer $transfer, int $shopId): bool
    {
        if ($transfer->source_type === Shop::class && (int) $transfer->source_id === $shopId) {
            return true;
        }

        return $transfer->destination_type === Shop::class
            && (int) $transfer->destination_id === $shopId;
    }

    public function pendingDispatchCount(User $user): int
    {
        if (! $user->can('transfers.view') || ! $user->can('transfers.dispatch')) {
            return 0;
        }

        $query = StockTransfer::query()->where('status', 'approved');

        if ($this->isAdministrator($user)) {
            return $query->count();
        }

        $warehouseId = $this->scopedWarehouseId($user);
        $shopId = $this->scopedShopId($user);

        if (! $warehouseId && ! $shopId) {
            return 0;
        }

        return $query->where(function (Builder $q) use ($warehouseId, $shopId) {
            if ($warehouseId) {
                $q->where(function (Builder $sub) use ($warehouseId) {
                    $sub->where('source_type', Warehouse::class)->where('source_id', $warehouseId);
                });
            }

            if ($shopId) {
                $method = $warehouseId ? 'orWhere' : 'where';
                $q->{$method}(function (Builder $sub) use ($shopId) {
                    $sub->where('source_type', Shop::class)->where('source_id', $shopId);
                });
            }
        })->count();
    }

    public function awaitingReceiveCount(User $user): int
    {
        if (! $user->can('transfers.view') || ! $user->can('transfers.receive')) {
            return 0;
        }

        $query = StockTransfer::query()->where('status', 'in_transit');

        if ($this->isAdministrator($user)) {
            return $query->count();
        }

        $shopId = $this->scopedShopId($user);
        $warehouseId = $this->scopedWarehouseId($user);

        if (! $shopId && ! $warehouseId) {
            return 0;
        }

        return $query->where(function (Builder $q) use ($shopId, $warehouseId) {
            if ($shopId) {
                $q->where(function (Builder $sub) use ($shopId) {
                    $sub->where('destination_type', Shop::class)->where('destination_id', $shopId);
                });
            }

            if ($warehouseId) {
                $method = $shopId ? 'orWhere' : 'where';
                $q->{$method}(function (Builder $sub) use ($warehouseId) {
                    $sub->where('destination_type', Warehouse::class)->where('destination_id', $warehouseId);
                });
            }
        })->count();
    }

    public function pendingApprovalCount(User $user): int
    {
        if (! $user->can('transfers.view')) {
            return 0;
        }

        $query = Approval::query()
            ->pending()
            ->where('approvable_type', StockTransfer::class);

        if ($this->isAdministrator($user)) {
            return $query->count();
        }

        if (! $user->can('approvals.act')) {
            return 0;
        }

        return $query->where('current_approver_id', $user->id)->count();
    }

    private function involvesWarehouse(StockTransfer $transfer, int $warehouseId): bool
    {
        return $this->warehouses->involvesWarehouse(
            $warehouseId,
            $transfer->source_type,
            $transfer->source_id,
            $transfer->destination_type,
            $transfer->destination_id,
        );
    }
}

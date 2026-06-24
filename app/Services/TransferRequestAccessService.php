<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;

class TransferRequestAccessService
{
    public function scopedShopId(User $user): ?int
    {
        if ($user->hasRole('Shop Manager') && $user->shop_id) {
            return (int) $user->shop_id;
        }

        return null;
    }

    public function scopedWarehouseId(User $user): ?int
    {
        return app(WarehouseAccessService::class)->scopedWarehouseId($user);
    }

    public function isAdministrator(User $user): bool
    {
        return $user->hasRole('Administrator');
    }

    public function canCreate(User $user): bool
    {
        if (! $user->can('transfer_requests.create')) {
            return false;
        }

        return $this->isAdministrator($user) || $this->scopedShopId($user) !== null;
    }

    public function canView(User $user, TransferRequest $request): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        $shopId = $this->scopedShopId($user);
        if ($shopId && $this->involvesShop($request, $shopId)) {
            return true;
        }

        if ($user->can('transfer_requests.review') && $this->isReviewerParty($user, $request)) {
            return true;
        }

        return $this->canReview($user, $request);
    }

    public function canManage(User $user, TransferRequest $request): bool
    {
        if (! $user->can('transfer_requests.create')) {
            return false;
        }

        if ($this->isAdministrator($user)) {
            return (int) $request->requested_by === $user->id;
        }

        $shopId = $this->scopedShopId($user);

        return $shopId
            && $request->destination_type === Shop::class
            && (int) $request->destination_id === $shopId
            && (int) $request->requested_by === $user->id;
    }

    public function canReview(User $user, TransferRequest $request): bool
    {
        if (! $user->can('transfer_requests.review')) {
            return false;
        }

        if ($request->status !== 'submitted') {
            return false;
        }

        if ($request->type === 'warehouse_to_shop') {
            $warehouseId = $this->scopedWarehouseId($user);

            return $warehouseId
                && $request->source_type === Warehouse::class
                && (int) $request->source_id === $warehouseId;
        }

        if ($request->type === 'inter_shop') {
            $shopId = $this->scopedShopId($user);

            return $shopId
                && $request->source_type === Shop::class
                && (int) $request->source_id === $shopId;
        }

        return false;
    }

    public function canCreateTransferFrom(User $user, TransferRequest $request): bool
    {
        if ($request->status !== 'accepted' || $request->stock_transfer_id) {
            return false;
        }

        if (! $user->can('transfers.create') && ! $this->isAdministrator($user)) {
            return false;
        }

        return $this->isReviewerParty($user, $request);
    }

    private function isReviewerParty(User $user, TransferRequest $request): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($request->type === 'warehouse_to_shop') {
            $warehouseId = $this->scopedWarehouseId($user);

            return $warehouseId
                && $request->source_type === Warehouse::class
                && (int) $request->source_id === $warehouseId;
        }

        if ($request->type === 'inter_shop') {
            $shopId = $this->scopedShopId($user);

            return $shopId
                && $request->source_type === Shop::class
                && (int) $request->source_id === $shopId;
        }

        return false;
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
                    $sub->where('destination_type', Shop::class)->where('destination_id', $shopId);
                })->orWhere(function (Builder $sub) use ($shopId) {
                    $sub->where('source_type', Shop::class)->where('source_id', $shopId);
                });
            }

            if ($warehouseId) {
                $method = $shopId ? 'orWhere' : 'where';
                $q->{$method}(function (Builder $sub) use ($warehouseId) {
                    $sub->where('type', 'warehouse_to_shop')
                        ->where('source_type', Warehouse::class)
                        ->where('source_id', $warehouseId);
                });
            }
        });
    }

    public function pendingReviewCount(User $user): int
    {
        if (! $user->can('transfer_requests.review')) {
            return 0;
        }

        $query = TransferRequest::query()->where('status', 'submitted');

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
                    $sub->where('type', 'warehouse_to_shop')
                        ->where('source_type', Warehouse::class)
                        ->where('source_id', $warehouseId);
                });
            }

            if ($shopId) {
                $method = $warehouseId ? 'orWhere' : 'where';
                $q->{$method}(function (Builder $sub) use ($shopId) {
                    $sub->where('type', 'inter_shop')
                        ->where('source_type', Shop::class)
                        ->where('source_id', $shopId);
                });
            }
        })->count();
    }

    private function involvesShop(TransferRequest $request, int $shopId): bool
    {
        if ($request->destination_type === Shop::class && (int) $request->destination_id === $shopId) {
            return true;
        }

        return $request->source_type === Shop::class
            && (int) $request->source_id === $shopId;
    }
}

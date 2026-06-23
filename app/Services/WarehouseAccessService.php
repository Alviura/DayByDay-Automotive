<?php

namespace App\Services;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;

class WarehouseAccessService
{
    public function scopedWarehouseId(User $user): ?int
    {
        if ($user->hasRole('Warehouse Manager') && $user->warehouse_id) {
            return (int) $user->warehouse_id;
        }

        return null;
    }

    public function isWarehouseManager(User $user): bool
    {
        return $this->scopedWarehouseId($user) !== null;
    }

    public function isAdministrator(User $user): bool
    {
        return $user->hasRole('Administrator');
    }

    public function scopeWarehouseMorph(Builder $query, string $typeColumn, string $idColumn, int $warehouseId): Builder
    {
        return $query
            ->where($typeColumn, Warehouse::class)
            ->where($idColumn, $warehouseId);
    }

    public function involvesWarehouse(int $warehouseId, ?string $sourceType, ?int $sourceId, ?string $destinationType, ?int $destinationId): bool
    {
        if ($sourceType === Warehouse::class && (int) $sourceId === $warehouseId) {
            return true;
        }

        return $destinationType === Warehouse::class
            && (int) $destinationId === $warehouseId;
    }

    public function canUseWarehouseLocation(User $user, string $locationType, int $locationId): bool
    {
        $scoped = $this->scopedWarehouseId($user);

        if (! $scoped) {
            return true;
        }

        return $locationType === 'warehouse' && $locationId === $scoped;
    }

    public function canUseWarehouseId(User $user, int $warehouseId): bool
    {
        $scoped = $this->scopedWarehouseId($user);

        if (! $scoped) {
            return true;
        }

        return $warehouseId === $scoped;
    }
}

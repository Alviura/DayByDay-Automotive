<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportFilters
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public ?int $shopId = null,
        public ?int $warehouseId = null,
        public ?int $supplierId = null,
        public ?string $preset = null,
        public bool $comparePrevious = false,
    ) {}

    public static function fromRequest(Request $request, ?ReportScopeService $scope = null): self
    {
        $scope ??= app(ReportScopeService::class);
        $preset = $request->filled('preset') ? (string) $request->preset : null;

        [$from, $to] = self::resolveDates($request, $preset);

        $shopId = $scope->scopedShopId()
            ?? ($request->filled('shop_id') ? (int) $request->shop_id : null);

        $warehouseId = $scope->scopedWarehouseId()
            ?? ($request->filled('warehouse_id') ? (int) $request->warehouse_id : null);

        return new self(
            $from,
            $to,
            $shopId,
            $warehouseId,
            $request->filled('supplier_id') ? (int) $request->supplier_id : null,
            $preset,
            $request->boolean('compare_previous'),
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private static function resolveDates(Request $request, ?string $preset): array
    {
        if ($request->filled('date_from') || $request->filled('date_to')) {
            return [
                $request->filled('date_from')
                    ? Carbon::parse($request->date_from)->startOfDay()
                    : now()->subDays(30)->startOfDay(),
                $request->filled('date_to')
                    ? Carbon::parse($request->date_to)->endOfDay()
                    : now()->endOfDay(),
            ];
        }

        return match ($preset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30d' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'mtd' => [now()->startOfMonth()->startOfDay(), now()->endOfDay()],
            'ytd' => [now()->startOfYear()->startOfDay(), now()->endOfDay()],
            default => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
        };
    }

    public function withShopId(int $shopId): self
    {
        return new self(
            $this->from,
            $this->to,
            $shopId,
            $this->warehouseId,
            $this->supplierId,
            $this->preset,
            $this->comparePrevious,
        );
    }

    public function withWarehouseId(int $warehouseId): self
    {
        return new self(
            $this->from,
            $this->to,
            $this->shopId,
            $warehouseId,
            $this->supplierId,
            $this->preset,
            $this->comparePrevious,
        );
    }

    public function previousPeriod(): self
    {
        $days = max(1, $this->from->diffInDays($this->to) + 1);

        return new self(
            $this->from->copy()->subDays($days)->startOfDay(),
            $this->to->copy()->subDays($days)->endOfDay(),
            $this->shopId,
            $this->warehouseId,
            $this->supplierId,
            $this->preset,
            $this->comparePrevious,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toQueryArray(): array
    {
        return array_filter([
            'date_from' => $this->from->format('Y-m-d'),
            'date_to' => $this->to->format('Y-m-d'),
            'shop_id' => $this->shopId,
            'warehouse_id' => $this->warehouseId,
            'supplier_id' => $this->supplierId,
            'preset' => $this->preset,
            'compare_previous' => $this->comparePrevious ? '1' : null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}

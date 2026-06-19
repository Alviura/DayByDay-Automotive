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
    ) {}

    public static function fromRequest(Request $request, ?int $scopedShopId = null): self
    {
        $shopId = $scopedShopId ?? ($request->filled('shop_id') ? (int) $request->shop_id : null);

        return new self(
            $request->filled('date_from')
                ? Carbon::parse($request->date_from)->startOfDay()
                : now()->subDays(30)->startOfDay(),
            $request->filled('date_to')
                ? Carbon::parse($request->date_to)->endOfDay()
                : now()->endOfDay(),
            $shopId,
        );
    }
}

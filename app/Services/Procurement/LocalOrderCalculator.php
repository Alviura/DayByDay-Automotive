<?php

namespace App\Services\Procurement;

use App\Models\QuotationItem;
use App\Models\Product;
use Illuminate\Support\Collection;

class LocalOrderCalculator
{
    public function calculateLine(QuotationItem $item, Product $product): QuotationItem
    {
        $orderQuantity = QuotationQuantityResolver::orderQuantity($item, $product);
        $stockQuantity = QuotationQuantityResolver::stockQuantity($item, $product);
        $unitPrice = (float) $item->unit_price;
        $transport = (float) ($item->transport ?? 0);
        $wholesale = $item->resolveMarketWholesalePrice($product);

        $totalPurchase = $unitPrice * $orderQuantity;
        $actualTotalCost = $totalPurchase + $transport;
        $unitCostArrival = $stockQuantity > 0 ? $actualTotalCost / $stockQuantity : 0;
        $marginAmount = $wholesale - $unitCostArrival;
        $marginPercent = $wholesale > 0 ? ($marginAmount / $wholesale) * 100 : 0;
        $expectedSales = $wholesale * $stockQuantity;
        $expectedMargin = $marginAmount * $stockQuantity;

        $item->fill([
            'order_quantity' => round($orderQuantity, 2),
            'quantity' => $stockQuantity,
            'market_wholesale_price' => round($wholesale, 2),
            'total_purchase_price' => round($totalPurchase, 2),
            'transport' => round($transport, 2),
            'actual_total_cost' => round($actualTotalCost, 2),
            'unit_cost_arrival' => round($unitCostArrival, 2),
            'cost_per_unit' => round($unitCostArrival, 2),
            'margin_amount' => round($marginAmount, 2),
            'margin_percent' => round($marginPercent, 2),
            'expected_sales' => round($expectedSales, 2),
            'expected_margin' => round($expectedMargin, 2),
            'landing_cost' => round($actualTotalCost, 2),
            'total_cost' => round($totalPurchase, 2),
        ]);

        return $item;
    }

    /**
     * @param  Collection<int, QuotationItem>  $items
     * @return array<string, float>
     */
    public function summarize(Collection $items): array
    {
        return [
            'total_purchase_price' => round((float) $items->sum('total_purchase_price'), 2),
            'total_transport_cost' => round((float) $items->sum('transport'), 2),
            'total_actual_cost' => round((float) $items->sum('actual_total_cost'), 2),
            'total_expected_sales' => round((float) $items->sum('expected_sales'), 2),
            'total_expected_margin' => round((float) $items->sum('expected_margin'), 2),
            'total_cbm' => 0,
        ];
    }
}

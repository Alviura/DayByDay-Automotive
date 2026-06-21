<?php

namespace App\Services\Procurement;

use App\Models\QuotationItem;
use App\Models\QuotationSeries;
use App\Models\Product;
use Illuminate\Support\Collection;

class ImportOrderCalculator
{
    public function calculateLine(QuotationItem $item, Product $product, QuotationSeries $folder): QuotationItem
    {
        $quantity = (float) $item->quantity;
        $unitPriceForeign = (float) ($item->unit_price_foreign ?? $item->unit_price);
        $exchangeRate = (float) $folder->exchange_rate;
        $cbmRate = (float) $folder->cbm_rate;

        $width = (float) ($item->width ?? 0);
        $length = (float) ($item->length ?? 0);
        $height = (float) ($item->height ?? 0);
        $quantityPerPacket = max(0.01, (float) ($item->quantity_per_packet ?: 1));
        $numberOfPackets = self::deriveNumberOfPackets($quantity, $quantityPerPacket);

        $totalCostForeign = $unitPriceForeign * $quantity;
        $unitPriceKsh = $unitPriceForeign * $exchangeRate;
        $cbmPerPacket = $width * $length * $height;
        $totalCbm = $cbmPerPacket * $numberOfPackets;
        $transportPerUnit = $quantity > 0 ? ($totalCbm * $cbmRate) / $quantity : 0;
        $unitCostArrival = $unitPriceKsh + $transportPerUnit;
        $wholesale = $item->resolveMarketWholesalePrice($product);
        $marginAmount = $wholesale - $unitCostArrival;
        $marginPercent = $wholesale > 0 ? ($marginAmount / $wholesale) * 100 : 0;
        $actualTotalCost = $unitCostArrival * $quantity;
        $expectedSales = $wholesale * $quantity;
        $expectedMargin = $marginAmount * $quantity;

        $item->fill([
            'unit_price' => round($unitPriceForeign, 4),
            'unit_price_foreign' => round($unitPriceForeign, 4),
            'unit_price_ksh' => round($unitPriceKsh, 2),
            'quantity_per_packet' => $quantityPerPacket,
            'number_of_packets' => round($numberOfPackets, 2),
            'cbm_per_packet' => round($cbmPerPacket, 6),
            'total_cbm' => round($totalCbm, 4),
            'cbm' => round($totalCbm, 4),
            'transport_per_unit' => round($transportPerUnit, 2),
            'freight_charge' => round($transportPerUnit * $quantity, 2),
            'market_wholesale_price' => round($wholesale, 2),
            'total_purchase_price' => round($totalCostForeign, 2),
            'total_cost' => round($totalCostForeign, 2),
            'actual_total_cost' => round($actualTotalCost, 2),
            'landing_cost' => round($actualTotalCost, 2),
            'unit_cost_arrival' => round($unitCostArrival, 2),
            'cost_per_unit' => round($unitCostArrival, 2),
            'margin_amount' => round($marginAmount, 2),
            'margin_percent' => round($marginPercent, 2),
            'expected_sales' => round($expectedSales, 2),
            'expected_margin' => round($expectedMargin, 2),
        ]);

        return $item;
    }

    public static function deriveNumberOfPackets(float $quantity, float $quantityPerPacket): float
    {
        $quantityPerPacket = max(0.01, $quantityPerPacket ?: 1);

        return round($quantity / $quantityPerPacket, 2);
    }

    /**
     * @param  Collection<int, QuotationItem>  $items
     * @return array<string, float>
     */
    public function summarize(Collection $items): array
    {
        return [
            'total_purchase_price' => round((float) $items->sum('total_purchase_price'), 2),
            'total_cbm' => round((float) $items->sum('total_cbm'), 4),
            'total_transport_cost' => round((float) $items->sum(fn (QuotationItem $item) => (float) $item->transport_per_unit * (float) $item->quantity), 2),
            'total_actual_cost' => round((float) $items->sum('actual_total_cost'), 2),
            'total_expected_sales' => round((float) $items->sum('expected_sales'), 2),
            'total_expected_margin' => round((float) $items->sum('expected_margin'), 2),
        ];
    }
}

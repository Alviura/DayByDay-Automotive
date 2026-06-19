<?php

namespace App\Services;

use App\Models\ProcurementFolder;
use App\Models\ProcurementItem;

class CostAnalysisService
{
    /**
     * Allocate folder-level freight and tax across lines (by CBM, else by line cost),
     * then compute landing cost, cost-per-unit, and recommended selling price.
     */
    public function analyze(
        ProcurementFolder $folder,
        ?float $totalFreight = null,
        ?float $totalTax = null,
        float $defaultMargin = 30
    ): ProcurementFolder {
        $folder->load('items.product');

        if ($folder->items->isEmpty()) {
            throw new \InvalidArgumentException('Add at least one line item before running cost analysis.');
        }

        $totalFreight = $totalFreight ?? (float) $folder->total_freight;
        $totalTax = $totalTax ?? (float) $folder->total_tax;
        $totalCbm = (float) $folder->items->sum('cbm');

        foreach ($folder->items as $item) {
            $item->total_cost = (float) $item->quantity * (float) $item->unit_cost;
            $item->save();
        }

        $totalItemCost = (float) $folder->items->sum('total_cost');

        foreach ($folder->items as $item) {
            $weight = $this->allocationWeight($item, $totalCbm, $totalItemCost);

            $item->freight_charge = round($totalFreight * $weight, 2);
            $item->tax_cost = round($totalTax * $weight, 2);
            $item->landing_cost = $item->total_cost + $item->freight_charge + $item->tax_cost;
            $item->cost_per_unit = $item->quantity > 0
                ? round($item->landing_cost / (float) $item->quantity, 2)
                : 0;

            $margin = $item->margin ?? $defaultMargin;
            $item->recommended_selling_price = round($item->cost_per_unit * (1 + ($margin / 100)), 2);
            $item->save();
        }

        $folder->update([
            'total_cost' => $folder->items()->sum('total_cost'),
            'total_freight' => $totalFreight,
            'total_tax' => $totalTax,
            'total_landing_cost' => $folder->items()->sum('landing_cost'),
            'status' => 'cost_analysis',
        ]);

        return $folder->fresh(['items.product', 'supplier']);
    }

    private function allocationWeight(ProcurementItem $item, float $totalCbm, float $totalItemCost): float
    {
        if ($totalCbm > 0 && $item->cbm) {
            return (float) $item->cbm / $totalCbm;
        }

        if ($totalItemCost > 0) {
            return (float) $item->total_cost / $totalItemCost;
        }

        return 0;
    }
}

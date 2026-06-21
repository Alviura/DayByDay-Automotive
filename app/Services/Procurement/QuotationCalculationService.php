<?php

namespace App\Services\Procurement;

use App\Models\QuotationSeries;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuotationCalculationService
{
    public function __construct(
        private LocalOrderCalculator $localCalculator,
        private ImportOrderCalculator $importCalculator,
    ) {}

    public function calculate(QuotationSeries $series): QuotationSeries
    {
        $series->load(['items.product']);

        if ($series->items->isEmpty()) {
            throw new InvalidArgumentException('Add at least one line item before calculating.');
        }

        if ($series->isImport()) {
            $this->assertImportRates($series);
        }

        $this->assertPricesPresent($series);

        return DB::transaction(function () use ($series) {
            $calculator = $series->isImport() ? $this->importCalculator : $this->localCalculator;

            foreach ($series->items as $item) {
                if ($series->isImport()) {
                    $this->importCalculator->calculateLine($item, $item->product, $series);
                } else {
                    $this->localCalculator->calculateLine($item, $item->product);
                }
                $item->save();
            }

            $series->refresh()->load('items');
            $totals = $calculator->summarize($series->items);

            $series->update([
                'total_purchase_price' => $totals['total_purchase_price'],
                'total_cbm' => $totals['total_cbm'] ?: null,
                'total_transport_cost' => $totals['total_transport_cost'],
                'total_actual_cost' => $totals['total_actual_cost'],
                'total_expected_sales' => $totals['total_expected_sales'],
                'total_expected_margin' => $totals['total_expected_margin'],
                'total_cost' => $totals['total_purchase_price'],
                'total_landing_cost' => $totals['total_actual_cost'],
                'total_freight' => $totals['total_transport_cost'],
            ]);

            return $series->fresh(['items.product', 'supplier']);
        });
    }

    public function confirmOrder(QuotationSeries $series, User $user): QuotationSeries
    {
        if (! $series->canConfirm()) {
            throw new InvalidArgumentException('This quotation series cannot be confirmed yet.');
        }

        if ($series->status === 'order_draft' && ! $series->total_actual_cost) {
            $series = $this->calculate($series);
        }

        $series->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return $series->fresh(['items.product', 'supplier']);
    }

    private function assertImportRates(QuotationSeries $series): void
    {
        if ((float) $series->exchange_rate <= 0) {
            throw new InvalidArgumentException('Conversion(R) is required for import orders.');
        }

        if ((float) $series->cbm_rate <= 0) {
            throw new InvalidArgumentException('CBM(R) is required for import orders.');
        }
    }

    private function assertPricesPresent(QuotationSeries $series): void
    {
        foreach ($series->items as $item) {
            if ($series->isImport()) {
                if ((float) ($item->unit_price_foreign ?? $item->unit_price) <= 0) {
                    throw new InvalidArgumentException("Enter supplier price for {$item->product->part_number}.");
                }
            } elseif ((float) $item->unit_price <= 0) {
                throw new InvalidArgumentException("Enter supplier price for {$item->product->part_number}.");
            }
        }
    }
}

<?php

namespace App\Services\Procurement;

use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationSeries;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuotationSeriesService
{
    public function createFromSupplier(Supplier $supplier, array $data, User $user): QuotationSeries
    {
        $purchaseType = $data['purchase_type'] ?? $supplier->purchase_type ?? 'local';
        $isImport = $purchaseType === 'import';

        return QuotationSeries::create([
            'series_number' => QuotationSeries::generateNumber(),
            'title' => QuotationSeries::generateTitle($supplier, $data['description'] ?? null),
            'description' => $data['description'] ?? null,
            'supplier_id' => $supplier->id,
            'currency' => strtoupper($data['currency'] ?? $supplier->currency ?? 'KES'),
            'exchange_rate' => $isImport ? ($data['exchange_rate'] ?? 1) : 1,
            'purchase_type' => $purchaseType,
            'import_type' => $purchaseType,
            'cbm_rate' => $isImport ? ($data['cbm_rate'] ?? null) : null,
            'status' => 'quotation_draft',
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
        ]);
    }

    public function bulkAddItems(QuotationSeries $series, array $lines): void
    {
        if (! $series->canBulkAddItems()) {
            throw new InvalidArgumentException('Cannot add items to this quotation series.');
        }

        DB::transaction(function () use ($series, $lines) {
            foreach ($lines as $line) {
                $productId = (int) $line['product_id'];
                $quantity = (float) $line['quantity'];

                if ($quantity <= 0) {
                    continue;
                }

                $existing = $series->items()->where('product_id', $productId)->first();

                if ($existing) {
                    $existing->update(['quantity' => $quantity]);
                } else {
                    QuotationItem::create([
                        'quotation_series_id' => $series->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);
                }
            }
        });
    }

    public function proceedToOrder(QuotationSeries $series): QuotationSeries
    {
        if (! $series->canProceedToOrder()) {
            throw new InvalidArgumentException('Add at least one product before proceeding to order processing.');
        }

        if ($series->isImport()) {
            if ((float) $series->exchange_rate <= 0 || (float) $series->cbm_rate <= 0) {
                throw new InvalidArgumentException('Set Conversion(R) and CBM(R) before order processing.');
            }
        }

        $series->update(['status' => 'order_draft']);

        return $series->fresh(['items.product', 'supplier']);
    }

    public function updatePrices(QuotationSeries $series, array $lines): void
    {
        if (! $series->canEditPrices()) {
            throw new InvalidArgumentException('Prices cannot be edited on this quotation series.');
        }

        DB::transaction(function () use ($series, $lines) {
            foreach ($lines as $line) {
                $item = $series->items()->where('id', $line['id'])->firstOrFail();
                $item->loadMissing('product');
                $marketWholesale = $this->resolveMarketWholesaleForSave($item, $line);

                if ($series->isImport()) {
                    $quantity = (float) $item->quantity;
                    $qtyPerPacket = max(0.01, (float) ($line['quantity_per_packet'] ?? 1));
                    $derivedPackets = ImportOrderCalculator::deriveNumberOfPackets($quantity, $qtyPerPacket);
                    $override = filter_var($line['packets_override'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $numberOfPackets = $override
                        ? round((float) ($line['number_of_packets'] ?? $derivedPackets), 2)
                        : $derivedPackets;

                    $item->update([
                        'unit_price_foreign' => $line['unit_price_foreign'] ?? $line['unit_price'] ?? null,
                        'unit_price' => $line['unit_price'] ?? $line['unit_price_foreign'] ?? null,
                        'width' => $line['width'] ?? null,
                        'length' => $line['length'] ?? null,
                        'height' => $line['height'] ?? null,
                        'quantity_per_packet' => $qtyPerPacket,
                        'number_of_packets' => $numberOfPackets,
                        'market_wholesale_price' => $marketWholesale,
                    ]);
                } else {
                    $item->update([
                        'unit_price' => $line['unit_price'] ?? null,
                        'transport' => $line['transport'] ?? 0,
                        'market_wholesale_price' => $marketWholesale,
                    ]);
                }
            }

            $this->clearCalculationState($series->fresh(['items']));
        });
    }

    private function clearCalculationState(QuotationSeries $series): void
    {
        $series->items()->update([
            'unit_price_ksh' => null,
            'cbm_per_packet' => null,
            'total_cbm' => null,
            'transport_per_unit' => null,
            'unit_cost_arrival' => null,
            'cost_per_unit' => 0,
            'margin_amount' => null,
            'margin_percent' => null,
            'total_purchase_price' => null,
            'actual_total_cost' => null,
            'expected_sales' => null,
            'expected_margin' => null,
            'landing_cost' => 0,
            'total_cost' => 0,
            'freight_charge' => 0,
            'cbm' => null,
            'margin' => null,
        ]);

        $series->update([
            'total_purchase_price' => 0,
            'total_cbm' => null,
            'total_transport_cost' => 0,
            'total_actual_cost' => 0,
            'total_expected_sales' => 0,
            'total_expected_margin' => 0,
            'total_cost' => 0,
            'total_landing_cost' => 0,
            'total_freight' => 0,
        ]);
    }

    private function resolveMarketWholesaleForSave(QuotationItem $item, array $line): ?float
    {
        $override = filter_var($line['market_wholesale_override'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $override) {
            return null;
        }

        $fallback = (float) ($item->product->min_selling_price ?? 0);

        return round((float) ($line['market_wholesale_price'] ?? $fallback), 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function quotationExportRows(QuotationSeries $series): array
    {
        $series->load(['items.product.productName', 'items.product.vehicleMake', 'items.product.vehicleModel', 'items.product.unit']);

        $rows = [];
        $index = 1;

        foreach ($series->items as $item) {
            $product = $item->product;
            $rows[] = [
                'ns' => $index++,
                'part_number' => $product->part_number,
                'product_name' => $product->productName?->name ?? $product->name,
                'make' => $product->vehicleMake?->name ?? '',
                'vehicle' => $product->vehicleModel?->name ?? '',
                'unit' => $product->unit?->abbreviation ?? $product->unit?->name ?? '',
                'quantity' => (float) $item->quantity,
                'unit_price' => $item->unit_price ? number_format((float) $item->unit_price, 2) : '—',
            ];
        }

        return $rows;
    }
}

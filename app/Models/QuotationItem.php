<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_series_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_price_foreign',
        'unit_price_ksh',
        'transport',
        'width',
        'length',
        'height',
        'quantity_per_packet',
        'number_of_packets',
        'cbm_per_packet',
        'total_cbm',
        'transport_per_unit',
        'unit_cost_arrival',
        'market_wholesale_price',
        'margin_amount',
        'margin_percent',
        'total_purchase_price',
        'actual_total_cost',
        'expected_sales',
        'expected_margin',
        'unit_cost',
        'cbm',
        'freight_charge',
        'tax_cost',
        'total_cost',
        'landing_cost',
        'cost_per_unit',
        'margin',
        'recommended_selling_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'unit_price_foreign' => 'decimal:4',
        'unit_price_ksh' => 'decimal:2',
        'transport' => 'decimal:2',
        'width' => 'decimal:4',
        'length' => 'decimal:4',
        'height' => 'decimal:4',
        'quantity_per_packet' => 'decimal:2',
        'number_of_packets' => 'decimal:2',
        'cbm_per_packet' => 'decimal:6',
        'total_cbm' => 'decimal:4',
        'transport_per_unit' => 'decimal:2',
        'unit_cost_arrival' => 'decimal:2',
        'market_wholesale_price' => 'decimal:2',
        'margin_amount' => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'total_purchase_price' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
        'expected_sales' => 'decimal:2',
        'expected_margin' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'cbm' => 'decimal:4',
        'freight_charge' => 'decimal:2',
        'tax_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'landing_cost' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'margin' => 'decimal:2',
        'recommended_selling_price' => 'decimal:2',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(QuotationSeries::class, 'quotation_series_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hasPrice(): bool
    {
        return (float) ($this->unit_price_foreign ?? $this->unit_price) > 0;
    }

    public function landedUnitCost(): float
    {
        return (float) ($this->unit_cost_arrival ?? $this->cost_per_unit ?? 0);
    }

    public function resolveMarketWholesalePrice(?Product $product = null): float
    {
        $product ??= $this->product;

        return (float) ($this->market_wholesale_price ?? $product?->min_selling_price ?? 0);
    }
}

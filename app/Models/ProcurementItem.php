<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementItem extends Model
{
    protected $fillable = [
        'procurement_folder_id',
        'product_id',
        'quantity',
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

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ProcurementFolder::class, 'procurement_folder_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

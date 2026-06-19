<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'system_quantity',
        'counted_quantity',
        'difference',
        'unit_cost',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'counted_quantity' => 'decimal:2',
        'difference' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (StockAdjustmentItem $item) {
            $item->difference = $item->counted_quantity - $item->system_quantity;
        });
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Fast-read stock snapshot per product per location (M12).
 * Minimal model here so Warehouse can declare its morphMany relation.
 */
class StockBalance extends Model
{
    protected $fillable = [
        'product_id',
        'location_type',
        'location_id',
        'quantity_on_hand',
        'quantity_reserved',
        'average_cost',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'quantity_available' => 'decimal:2',
        'average_cost' => 'decimal:2',
    ];

    public function location(): MorphTo
    {
        return $this->morphTo();
    }
}

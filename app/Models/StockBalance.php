<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForLocation($query, Model $location)
    {
        return $query->where('location_type', $location->getMorphClass())
            ->where('location_id', $location->getKey());
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->whereHas('product', function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('part_number', 'like', "%{$term}%");
        });
    }

    public function stockValue(): float
    {
        return (float) $this->quantity_on_hand * (float) $this->average_cost;
    }
}

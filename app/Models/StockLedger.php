<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockLedger extends Model
{
    public $timestamps = true;

    protected $table = 'stock_ledger';

    protected $fillable = [
        'product_id',
        'location_type',
        'location_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'balance_after',
        'reference_type',
        'reference_id',
        'reference_number',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForLocation($query, Model $location)
    {
        return $query->where('location_type', $location->getMorphClass())
            ->where('location_id', $location->getKey());
    }

    public function transactionLabel(): string
    {
        return str_replace('_', ' ', ucwords($this->transaction_type, '_'));
    }
}

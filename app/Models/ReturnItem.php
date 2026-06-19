<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    protected $fillable = [
        'return_id',
        'product_id',
        'quantity',
        'unit_price',
        'condition',
        'restock',
        'replacement',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'restock' => 'boolean',
        'replacement' => 'boolean',
    ];

    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(ReturnRecord::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineRefund(): float
    {
        return (float) $this->quantity * (float) ($this->unit_price ?? 0);
    }

    public function conditionLabel(): string
    {
        return ucfirst($this->condition);
    }
}

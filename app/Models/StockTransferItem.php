<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity',
        'dispatched_quantity',
        'received_quantity',
        'damaged_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'dispatched_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
    ];

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transferQuantity(): float
    {
        return (float) ($this->quantity > 0 ? $this->quantity : $this->dispatched_quantity);
    }

    public function goodQuantity(): float
    {
        return max(0, (float) $this->received_quantity - (float) $this->damaged_quantity);
    }

    public function remainingQuantity(): float
    {
        return max(0, (float) $this->dispatched_quantity - (float) $this->received_quantity);
    }
}

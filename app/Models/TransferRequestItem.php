<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferRequestItem extends Model
{
    protected $fillable = [
        'transfer_request_id',
        'product_id',
        'requested_quantity',
        'approved_quantity',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:2',
        'approved_quantity' => 'decimal:2',
    ];

    public function transferRequest(): BelongsTo
    {
        return $this->belongsTo(TransferRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function dispatchQuantity(): float
    {
        return (float) ($this->approved_quantity ?? $this->requested_quantity);
    }
}

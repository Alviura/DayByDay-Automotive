<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptNoteItem extends Model
{
    protected $fillable = [
        'goods_receipt_note_id',
        'product_id',
        'expected_quantity',
        'received_quantity',
        'damaged_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function goodsReceiptNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function goodQuantity(): float
    {
        return max(0, (float) $this->received_quantity - (float) $this->damaged_quantity);
    }
}

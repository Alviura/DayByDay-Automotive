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

    protected static function booted(): void
    {
        static::saving(function (GoodsReceiptNoteItem $item) {
            foreach (['expected_quantity', 'received_quantity', 'damaged_quantity'] as $attribute) {
                if ($item->{$attribute} !== null) {
                    $item->{$attribute} = self::normalizeQuantity($item->{$attribute});
                }
            }
        });
    }

    public static function normalizeQuantity(float|string|null $value): float
    {
        $value = round((float) $value, 2);
        $nearest = round($value);

        if (abs($value - $nearest) < 0.05) {
            return (float) $nearest;
        }

        return $value;
    }

    public static function formatQuantity(float|string|null $value): string
    {
        $value = self::normalizeQuantity($value);

        return abs($value - round($value)) < 0.001
            ? number_format($value, 0)
            : number_format($value, 2);
    }

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
        $received = self::normalizeQuantity($this->received_quantity);
        $damaged = self::normalizeQuantity($this->damaged_quantity);

        return max(0, round($received - $damaged, 2));
    }
}

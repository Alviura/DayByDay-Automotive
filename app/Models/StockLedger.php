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

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        'opening_balance' => 'Opening Balance',
        'purchase_receipt' => 'Purchase Receipt',
        'purchase_receipt_void' => 'Receipt Void',
        'transfer_out' => 'Transfer Out',
        'transfer_in' => 'Transfer In',
        'sale' => 'Sale',
        'customer_return' => 'Customer Return',
        'supplier_return' => 'Supplier Return',
        'adjustment' => 'Adjustment',
    ];

    /** @var array<string, string> */
    public const TYPE_BADGES = [
        'opening_balance' => 'inv-badge inv-badge-slate',
        'purchase_receipt' => 'inv-badge inv-badge-green',
        'purchase_receipt_void' => 'inv-badge inv-badge-rose',
        'transfer_out' => 'inv-badge inv-badge-indigo',
        'transfer_in' => 'inv-badge inv-badge-blue',
        'sale' => 'inv-badge inv-badge-orange',
        'customer_return' => 'inv-badge inv-badge-teal',
        'supplier_return' => 'inv-badge inv-badge-amber',
        'adjustment' => 'inv-badge inv-badge-violet',
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
        return self::TYPE_LABELS[$this->transaction_type]
            ?? str_replace('_', ' ', ucwords($this->transaction_type, '_'));
    }

    public function badgeClass(): string
    {
        return self::TYPE_BADGES[$this->transaction_type] ?? 'inv-badge inv-badge-slate';
    }

    public function isInbound(): bool
    {
        return (float) $this->quantity > 0;
    }

    public function lineValue(): float
    {
        return abs((float) $this->quantity) * (float) ($this->unit_cost ?? 0);
    }

    public function referenceUrl(): ?string
    {
        if (! $this->reference_type || ! $this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {
            GoodsReceiptNote::class => route('goods-receipts.show', $this->reference_id),
            StockAdjustment::class => route('stock-adjustments.show', $this->reference_id),
            ReturnRecord::class => (($return = ReturnRecord::find($this->reference_id))
                ? ($return->type === 'supplier'
                    ? route('supplier-returns.show', $return)
                    : route('customer-returns.show', $return))
                : null),
            StockTransfer::class => route('stock-transfers.show', $this->reference_id),
            Sale::class => route('sales.show', $this->reference_id),
            default => null,
        };
    }

    public function referenceLabel(): ?string
    {
        if ($this->reference_number) {
            return $this->reference_number;
        }

        return $this->reference_type ? class_basename($this->reference_type).' #'.$this->reference_id : null;
    }
}

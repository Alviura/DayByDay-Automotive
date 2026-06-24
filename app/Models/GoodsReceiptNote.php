<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptNote extends Model
{
    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'quotation_series_id',
        'warehouse_id',
        'received_by',
        'received_at',
        'notes',
        'status',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'GRN-'.date('Y').'-';
        $last = static::where('grn_number', 'like', $prefix.'%')
            ->orderByDesc('grn_number')
            ->value('grn_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function quotationSeries(): BelongsTo
    {
        return $this->belongsTo(QuotationSeries::class, 'quotation_series_id');
    }

    /** @deprecated use quotationSeries() */
    public function folder(): BelongsTo
    {
        return $this->quotationSeries();
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function canVoid(): bool
    {
        return $this->isPosted();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'posted' => 'Posted',
            'voided' => 'Voided',
            default => ucfirst($this->status ?? 'posted'),
        };
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptNoteItem::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function totalReceivedQuantity(): float
    {
        return round((float) $this->items->sum(fn (GoodsReceiptNoteItem $item) => $item->normalizeQuantity($item->received_quantity)), 2);
    }

    public function totalDamagedQuantity(): float
    {
        return round((float) $this->items->sum(fn (GoodsReceiptNoteItem $item) => $item->normalizeQuantity($item->damaged_quantity)), 2);
    }

    public function totalGoodQuantity(): float
    {
        return round((float) $this->items->sum(fn (GoodsReceiptNoteItem $item) => $item->goodQuantity()), 2);
    }

    public function totalValue(): float
    {
        return (float) $this->items->sum(fn (GoodsReceiptNoteItem $item) => $item->goodQuantity() * (float) $item->unit_cost);
    }

    public function hasDamage(): bool
    {
        return $this->totalDamagedQuantity() > 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'quotation_series_id',
        'supplier_id',
        'status',
        'delivery_status',
        'order_date',
        'expected_date',
        'currency',
        'total',
        'notes',
        'closed_short_at',
        'closed_short_by',
        'close_short_reason',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total' => 'decimal:2',
        'closed_short_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'PO-'.date('Y').'-';
        $last = static::withTrashed()
            ->where('po_number', 'like', $prefix.'%')
            ->orderByDesc('po_number')
            ->value('po_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptNotes(): HasMany
    {
        return $this->hasMany(GoodsReceiptNote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedShortBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_short_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'partially_received' => 'Partially Received',
            'received' => 'Fully Received',
            'closed_short' => 'Closed Short',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function deliveryLabel(): string
    {
        return match ($this->delivery_status) {
            'pending' => 'Pending',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered',
            default => ucfirst($this->delivery_status),
        };
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['sent', 'partially_received'], true);
    }

    public function canCloseShort(): bool
    {
        if (! in_array($this->status, ['sent', 'partially_received'], true)) {
            return false;
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        return $items->contains(fn ($item) => $item->remainingQuantity() > 0.001);
    }

    public function isReceiptComplete(): bool
    {
        if ($this->status === 'closed_short') {
            return true;
        }

        $this->loadMissing('items');

        if ($this->items->isEmpty()) {
            return false;
        }

        return $this->items->every(
            fn ($item) => (float) $item->received_quantity >= (float) $item->quantity
        );
    }

    public function hasAnyReceipt(): bool
    {
        $this->loadMissing('items');

        return $this->items->contains(fn ($item) => (float) $item->received_quantity > 0);
    }

    public function receiptStateIsStale(): bool
    {
        if ($this->status === 'closed_short') {
            return false;
        }

        $this->loadMissing('items');

        $allReceived = $this->isReceiptComplete();
        $anyReceived = $this->hasAnyReceipt();

        $expectedStatus = $allReceived
            ? 'received'
            : ($anyReceived ? 'partially_received' : 'sent');

        if ($this->status !== $expectedStatus) {
            return true;
        }

        if (! $anyReceived && $this->delivery_status === 'delivered') {
            return true;
        }

        if ($allReceived && $this->delivery_status !== 'delivered') {
            return true;
        }

        return false;
    }

    public function totalShortQuantity(): float
    {
        return round((float) $this->items->sum(fn ($item) => $item->remainingQuantity()), 2);
    }

    public function totalOrderedQuantity(): float
    {
        return (float) $this->items->sum('quantity');
    }

    public function totalReceivedQuantity(): float
    {
        return (float) $this->items->sum('received_quantity');
    }

    public function receiptProgressPercent(): int
    {
        $ordered = $this->totalOrderedQuantity();

        if ($ordered <= 0) {
            return 0;
        }

        return (int) round(($this->totalReceivedQuantity() / $ordered) * 100);
    }
}

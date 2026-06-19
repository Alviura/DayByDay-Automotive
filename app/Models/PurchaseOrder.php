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
        'procurement_folder_id',
        'supplier_id',
        'status',
        'delivery_status',
        'order_date',
        'expected_date',
        'currency',
        'total',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total' => 'decimal:2',
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

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ProcurementFolder::class, 'procurement_folder_id');
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

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'partially_received' => 'Partially Received',
            'received' => 'Fully Received',
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
}

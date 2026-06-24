<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'supplier_id',
        'purchase_order_id',
        'goods_receipt_note_id',
        'supplier_invoice_number',
        'amount',
        'method',
        'reference',
        'paid_at',
        'paid_by',
        'notes',
        'status',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'SP-'.date('Y').'-';
        $last = static::where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('payment_number')
            ->value('payment_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceiptNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptNote::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
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
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'posted' => 'sp-badge sp-badge-green',
            'voided' => 'sp-badge sp-badge-rose',
            default => 'sp-badge sp-badge-slate',
        };
    }

    public function methodLabel(): string
    {
        return Payment::methods()[$this->method] ?? ucfirst(str_replace('_', ' ', $this->method));
    }

    public function methodIcon(): string
    {
        return match ($this->method) {
            'cash' => 'fa-money-bill-wave',
            'mpesa' => 'fa-mobile-screen',
            'card' => 'fa-credit-card',
            'bank_transfer' => 'fa-building-columns',
            'cheque' => 'fa-money-check',
            default => 'fa-wallet',
        };
    }

    public function methodPillClass(): string
    {
        return match ($this->method) {
            'cash' => 'sp-method-cash',
            'mpesa' => 'sp-method-mpesa',
            'card' => 'sp-method-card',
            'bank_transfer' => 'sp-method-bank',
            'cheque' => 'sp-method-cheque',
            default => 'sp-method-default',
        };
    }

    public function allocationLabel(): string
    {
        if ($this->goodsReceiptNote) {
            return $this->goodsReceiptNote->grn_number;
        }

        if ($this->purchaseOrder) {
            return $this->purchaseOrder->po_number;
        }

        return 'On account';
    }

    public function allocationType(): string
    {
        if ($this->goodsReceiptNote) {
            return 'grn';
        }

        if ($this->purchaseOrder) {
            return 'po';
        }

        return 'account';
    }
}

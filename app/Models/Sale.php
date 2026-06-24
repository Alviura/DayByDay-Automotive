<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'shop_id',
        'user_id',
        'sale_type',
        'customer_account_id',
        'vehicle_plate',
        'customer_invoice_id',
        'ordered_by',
        'completed_by',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'amount_paid',
        'change_due',
        'status',
        'payment_status',
        'sold_at',
        'submitted_at',
        'ar_recognized_at',
        'ar_invoiced_at',
        'reversed_by',
        'reversed_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_due' => 'decimal:2',
        'sold_at' => 'datetime',
        'submitted_at' => 'datetime',
        'ar_recognized_at' => 'datetime',
        'ar_invoiced_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public static function generateReceiptNumber(): string
    {
        $prefix = 'RCP-'.date('Y').'-';
        $last = static::withTrashed()
            ->where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class);
    }

    public function customerInvoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'sale_id')->where('type', 'customer');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function auditModule(): string
    {
        return 'sale';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->receipt_number;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'held' => 'At Cash Desk',
            'completed' => 'Completed',
            'reversed' => 'Reversed',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'held' => 'sl-badge sl-badge-amber',
            'completed' => 'sl-badge sl-badge-green',
            'reversed' => 'sl-badge sl-badge-rose',
            default => 'sl-badge sl-badge-slate',
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Unpaid',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'refunded' => 'Refunded',
            default => ucfirst($this->payment_status),
        };
    }

    public function canComplete(): bool
    {
        return $this->status === 'held' && $this->items()->exists();
    }

    public function canReverse(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        if ($this->customer_invoice_id) {
            return false;
        }

        return ! $this->returnRecords()->whereNotIn('status', ['rejected'])->exists();
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    public function isCredit(): bool
    {
        return $this->sale_type === 'credit';
    }

    public function isRetail(): bool
    {
        return $this->sale_type === 'retail';
    }

    public function saleTypeLabel(): string
    {
        return match ($this->sale_type) {
            'credit' => 'On Account',
            'retail' => 'Retail',
            default => ucfirst($this->sale_type ?? 'retail'),
        };
    }
}

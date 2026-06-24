<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_account_id',
        'period_start',
        'period_end',
        'subtotal',
        'tax_total',
        'total',
        'amount_paid',
        'status',
        'issued_at',
        'ar_consolidated_at',
        'due_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'issued_at' => 'datetime',
        'ar_consolidated_at' => 'datetime',
        'due_at' => 'date',
    ];

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Y').'-';
        $last = static::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class, 'customer_account_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerInvoicePayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft' => 'ci-badge ci-badge-slate',
            'sent' => 'ci-badge ci-badge-amber',
            'partially_paid' => 'ci-badge ci-badge-amber',
            'paid' => 'ci-badge ci-badge-green',
            default => 'ci-badge ci-badge-slate',
        };
    }

    public function isOverdue(): bool
    {
        return $this->balanceDue() > 0
            && $this->due_at
            && $this->due_at->isPast();
    }
}

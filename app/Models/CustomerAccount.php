<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerAccount extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'phone',
        'email',
        'billing_terms',
        'credit_limit',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('contact_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function outstandingBalance(): float
    {
        $salesTotal = (float) $this->sales()
            ->where('sale_type', 'credit')
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('total');

        $refunds = (float) ReturnRecord::query()
            ->where('type', 'customer')
            ->where('status', 'completed')
            ->whereHas('sale', fn ($q) => $q->where('customer_account_id', $this->id))
            ->sum('refund_amount');

        return max(0, $salesTotal - $refunds);
    }

    public function completedReturnRefunds(): float
    {
        return (float) ReturnRecord::query()
            ->where('type', 'customer')
            ->where('status', 'completed')
            ->whereHas('sale', fn ($q) => $q->where('customer_account_id', $this->id))
            ->sum('refund_amount');
    }

    public function unpaidCreditSales()
    {
        return $this->sales()
            ->where('sale_type', 'credit')
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNull('customer_invoice_id');
    }
}

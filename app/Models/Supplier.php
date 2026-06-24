<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'country',
        'currency',
        'purchase_type',
        'lead_time_days',
        'rating',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
        'rating' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Supplier $supplier) {
            if ($supplier->code) {
                $supplier->code = strtoupper(trim($supplier->code));
            }
            if ($supplier->currency) {
                $supplier->currency = strtoupper(trim($supplier->currency));
            }
        });
    }

    public function quotationSeries(): HasMany
    {
        return $this->hasMany(QuotationSeries::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierReturns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class)->where('type', 'supplier');
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function purchaseTypeLabel(): string
    {
        return match ($this->purchase_type) {
            'import' => 'Import',
            default => 'Local',
        };
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
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('contact_person', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%");
        });
    }
}

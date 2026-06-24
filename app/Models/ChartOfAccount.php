<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'account_type',
        'normal_balance',
        'parent_id',
        'shop_id',
        'payment_method',
        'is_active',
        'is_system',
        'description',
    ];

    protected $casts = [
        'account_type' => AccountType::class,
        'normal_balance' => NormalBalance::class,
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function typeLabel(): string
    {
        return $this->account_type->label();
    }

    public function typeIcon(): string
    {
        return $this->account_type->icon();
    }

    public function typePillClass(): string
    {
        return $this->account_type->pillClass();
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
            $q->where('code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }

    public function signedBalance(float $debits, float $credits): float
    {
        $net = round($debits - $credits, 2);

        return $this->normal_balance === NormalBalance::Debit ? $net : -$net;
    }
}

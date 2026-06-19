<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'sale_id',
        'method',
        'amount',
        'reference',
        'paid_at',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'cash' => 'Cash',
            'mpesa' => 'M-Pesa',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            default => ucfirst(str_replace('_', ' ', $this->method)),
        };
    }

    public static function methods(): array
    {
        return [
            'cash' => 'Cash',
            'mpesa' => 'M-Pesa',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
        ];
    }
}

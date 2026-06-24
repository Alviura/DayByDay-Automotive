<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRemittance extends Model
{
    protected $fillable = [
        'period_year',
        'period_month',
        'tax_collected',
        'amount_remitted',
        'status',
        'due_date',
        'filed_at',
        'paid_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'tax_collected' => 'decimal:2',
        'amount_remitted' => 'decimal:2',
        'due_date' => 'date',
        'filed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function periodLabel(): string
    {
        return Carbon::create($this->period_year, $this->period_month, 1)->format('F Y');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'filed' => 'Filed',
            'paid' => 'Paid',
            default => ucfirst($this->status),
        };
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->tax_collected - (float) $this->amount_remitted);
    }
}

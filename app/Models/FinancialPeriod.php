<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPeriod extends Model
{
    protected $fillable = [
        'period_year',
        'period_month',
        'status',
        'closed_at',
        'closed_by',
        'notes',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function periodLabel(): string
    {
        return Carbon::create($this->period_year, $this->period_month, 1)->format('F Y');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function startDate(): Carbon
    {
        return Carbon::create($this->period_year, $this->period_month, 1)->startOfMonth();
    }

    public function endDate(): Carbon
    {
        return $this->startDate()->copy()->endOfMonth();
    }
}

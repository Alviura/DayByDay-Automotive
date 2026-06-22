<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'run_number',
        'status',
        'processed_by',
        'locked_by',
        'locked_at',
        'paid_at',
        'total_gross',
        'total_deductions',
        'total_net',
        'total_employer_cost',
        'notes',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'total_employer_cost' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollLine::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'calculated'], true);
    }

    public static function generateNumber(PayrollPeriod $period): string
    {
        $prefix = sprintf('PR-%d%02d-', $period->year, $period->month);
        $count = static::where('run_number', 'like', "{$prefix}%")->count() + 1;

        return $prefix.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
    }
}

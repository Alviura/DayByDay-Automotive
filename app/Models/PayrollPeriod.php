<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'year',
        'month',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public function latestRun(): HasOne
    {
        return $this->hasOne(PayrollRun::class)->latestOfMany();
    }

    public function label(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['locked', 'paid'], true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'other_allowance',
        'payment_method',
        'bank_name',
        'account_number',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function grossPay(): float
    {
        return (float) $this->basic_salary
            + (float) $this->housing_allowance
            + (float) $this->transport_allowance
            + (float) $this->other_allowance;
    }
}

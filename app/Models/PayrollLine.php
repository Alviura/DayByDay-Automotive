<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLine extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'other_allowance',
        'gross_pay',
        'nssf_employee',
        'shif',
        'housing_levy_employee',
        'paye',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'nssf_employer',
        'housing_levy_employer',
        'calculation_snapshot',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'nssf_employee' => 'decimal:2',
        'shif' => 'decimal:2',
        'housing_levy_employee' => 'decimal:2',
        'paye' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'nssf_employer' => 'decimal:2',
        'housing_levy_employer' => 'decimal:2',
        'calculation_snapshot' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\User;
use App\Services\GlPostingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollService
{
    public function __construct(
        private readonly KenyaStatutoryCalculator $calculator,
        private readonly GlPostingService $gl,
    ) {}

    public function createPeriod(int $year, int $month): PayrollPeriod
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return PayrollPeriod::firstOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'draft',
            ]
        );
    }

    public function generateRun(PayrollPeriod $period, int $processedBy): PayrollRun
    {
        if ($period->isLocked()) {
            throw new RuntimeException('This payroll period is locked.');
        }

        return DB::transaction(function () use ($period, $processedBy) {
            PayrollRun::where('payroll_period_id', $period->id)
                ->whereIn('status', ['draft', 'calculated'])
                ->get()
                ->each(function (PayrollRun $old) {
                    $old->lines()->delete();
                    $old->delete();
                });

            $run = PayrollRun::create([
                'payroll_period_id' => $period->id,
                'run_number' => PayrollRun::generateNumber($period),
                'status' => 'calculated',
                'processed_by' => $processedBy,
            ]);

            $employees = Employee::onPayroll()
                ->with('currentSalary')
                ->orderBy('employee_number')
                ->get();

            $totals = [
                'gross' => 0,
                'deductions' => 0,
                'net' => 0,
                'employer' => 0,
            ];

            foreach ($employees as $employee) {
                $salary = $employee->currentSalary;
                if (! $salary) {
                    continue;
                }

                $line = $this->buildLine($run, $employee, $salary);
                $totals['gross'] += (float) $line->gross_pay;
                $totals['deductions'] += (float) $line->total_deductions;
                $totals['net'] += (float) $line->net_pay;
                $totals['employer'] += (float) $line->nssf_employer + (float) $line->housing_levy_employer;
            }

            $run->update([
                'total_gross' => $totals['gross'],
                'total_deductions' => $totals['deductions'],
                'total_net' => $totals['net'],
                'total_employer_cost' => $totals['employer'],
            ]);

            $period->update(['status' => 'calculated']);

            return $run->load(['lines.employee', 'processor']);
        });
    }

    public function lockRun(PayrollRun $run, int $lockedBy): PayrollRun
    {
        if (! $run->isEditable()) {
            throw new RuntimeException('This payroll run cannot be locked.');
        }

        $run->update([
            'status' => 'locked',
            'locked_by' => $lockedBy,
            'locked_at' => now(),
        ]);

        $run->period->update(['status' => 'locked']);

        $run = $run->fresh(['lines.employee', 'processor', 'locker']);
        $this->gl->postPayrollLocked($run, User::find($lockedBy));

        return $run;
    }

    public function markPaid(PayrollRun $run): PayrollRun
    {
        if ($run->status !== 'locked') {
            throw new RuntimeException('Only locked payroll runs can be marked as paid.');
        }

        $run->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $run->period->update(['status' => 'paid']);

        $run = $run->fresh(['lines']);
        $this->gl->postPayrollPaid($run);

        return $run;
    }

    private function buildLine(PayrollRun $run, Employee $employee, EmployeeSalary $salary): PayrollLine
    {
        $gross = $salary->grossPay();
        $calc = $this->calculator->calculate($gross);

        return PayrollLine::create([
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'basic_salary' => $salary->basic_salary,
            'housing_allowance' => $salary->housing_allowance,
            'transport_allowance' => $salary->transport_allowance,
            'other_allowance' => $salary->other_allowance,
            'gross_pay' => $calc['gross_pay'],
            'nssf_employee' => $calc['nssf_employee'],
            'shif' => $calc['shif'],
            'housing_levy_employee' => $calc['housing_levy_employee'],
            'paye' => $calc['paye'],
            'other_deductions' => $calc['other_deductions'],
            'total_deductions' => $calc['total_deductions'],
            'net_pay' => $calc['net_pay'],
            'nssf_employer' => $calc['nssf_employer'],
            'housing_levy_employer' => $calc['housing_levy_employer'],
            'calculation_snapshot' => $calc['snapshot'],
        ]);
    }
}

<?php

namespace App\Services\Payroll;

use App\Models\PayrollRun;
use Illuminate\Support\Collection;

class PayrollExportService
{
    public function registerRows(PayrollRun $run): Collection
    {
        $run->load(['lines.employee.shop', 'lines.employee.warehouse', 'lines.employee.currentSalary', 'period']);

        return $run->lines->map(function ($line) {
            $employee = $line->employee;

            return [
                'Employee Number' => $employee->employee_number,
                'Employee Name' => $employee->fullName(),
                'Job Title' => $employee->job_title,
                'Station' => $employee->stationLabel(),
                'Basic Salary' => $this->formatMoney($line->basic_salary),
                'Housing Allowance' => $this->formatMoney($line->housing_allowance),
                'Transport Allowance' => $this->formatMoney($line->transport_allowance),
                'Other Allowance' => $this->formatMoney($line->other_allowance),
                'Gross Pay' => $this->formatMoney($line->gross_pay),
                'NSSF (Employee)' => $this->formatMoney($line->nssf_employee),
                'SHIF' => $this->formatMoney($line->shif),
                'Housing Levy (Employee)' => $this->formatMoney($line->housing_levy_employee),
                'PAYE' => $this->formatMoney($line->paye),
                'Total Deductions' => $this->formatMoney($line->total_deductions),
                'Net Pay' => $this->formatMoney($line->net_pay),
                'NSSF (Employer)' => $this->formatMoney($line->nssf_employer),
                'Housing Levy (Employer)' => $this->formatMoney($line->housing_levy_employer),
            ];
        });
    }

    public function bankRows(PayrollRun $run): Collection
    {
        $run->load(['lines.employee.currentSalary']);

        return $run->lines->map(function ($line) {
            $employee = $line->employee;
            $salary = $employee->currentSalary;

            return [
                'Employee Number' => $employee->employee_number,
                'Employee Name' => $employee->fullName(),
                'Payment Method' => $salary?->payment_method ?? '',
                'Bank Name' => $salary?->bank_name ?? '',
                'Account Number' => $salary?->account_number ?? '',
                'Phone' => $employee->phone ?? '',
                'Net Pay' => $this->formatMoney($line->net_pay),
            ];
        });
    }

    public function registerTotals(PayrollRun $run): array
    {
        return [
            'Employee Number' => '',
            'Employee Name' => 'TOTALS',
            'Job Title' => '',
            'Station' => '',
            'Basic Salary' => '',
            'Housing Allowance' => '',
            'Transport Allowance' => '',
            'Other Allowance' => '',
            'Gross Pay' => $this->formatMoney($run->total_gross),
            'NSSF (Employee)' => '',
            'SHIF' => '',
            'Housing Levy (Employee)' => '',
            'PAYE' => '',
            'Total Deductions' => $this->formatMoney($run->total_deductions),
            'Net Pay' => $this->formatMoney($run->total_net),
            'NSSF (Employer)' => '',
            'Housing Levy (Employer)' => $this->formatMoney($run->total_employer_cost),
        ];
    }

    public function filename(PayrollRun $run, string $suffix): string
    {
        $run->loadMissing('period');

        return str($run->run_number)->slug('-').'-'.$suffix;
    }

    private function formatMoney(float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}

<?php

namespace App\Services\Payroll;

class KenyaStatutoryCalculator
{
    public function calculate(float $grossPay): array
    {
        $nssf = $this->nssf($grossPay);
        $shif = $this->shif($grossPay);
        $housingEmployee = $this->housingLevy($grossPay, 'employee_rate');
        $housingEmployer = $this->housingLevy($grossPay, 'employer_rate');
        $taxablePay = max(0, $grossPay - $nssf['employee'] - $shif);
        $paye = $this->paye($taxablePay);

        $totalDeductions = round($nssf['employee'] + $shif + $housingEmployee + $paye, 2);
        $netPay = round($grossPay - $totalDeductions, 2);
        $employerCost = round($nssf['employer'] + $housingEmployer, 2);

        return [
            'gross_pay' => round($grossPay, 2),
            'nssf_employee' => $nssf['employee'],
            'nssf_employer' => $nssf['employer'],
            'shif' => $shif,
            'housing_levy_employee' => $housingEmployee,
            'housing_levy_employer' => $housingEmployer,
            'taxable_pay' => round($taxablePay, 2),
            'paye' => $paye,
            'other_deductions' => 0,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'employer_cost' => $employerCost,
            'snapshot' => [
                'config' => config('payroll'),
                'taxable_pay' => round($taxablePay, 2),
            ],
        ];
    }

    private function nssf(float $gross): array
    {
        $tier1Limit = (float) config('payroll.nssf.tier1_limit');
        $tier2Limit = (float) config('payroll.nssf.tier2_limit');
        $rate = (float) config('payroll.nssf.rate');

        $tier1Base = min($gross, $tier1Limit);
        $tier2Base = max(0, min($gross, $tier2Limit) - $tier1Limit);
        $employee = round(($tier1Base + $tier2Base) * $rate, 2);

        return [
            'employee' => $employee,
            'employer' => $employee,
        ];
    }

    private function shif(float $gross): float
    {
        return round($gross * (float) config('payroll.shif_rate'), 2);
    }

    private function housingLevy(float $gross, string $key): float
    {
        return round($gross * (float) config("payroll.housing_levy.{$key}"), 2);
    }

    private function paye(float $taxablePay): float
    {
        if ($taxablePay <= 0) {
            return 0;
        }

        $tax = 0;
        $remaining = $taxablePay;
        $previousMax = 0;

        foreach (config('payroll.paye_bands') as $band) {
            $max = $band['max'] !== null ? (float) $band['max'] : PHP_FLOAT_MAX;
            $rate = (float) $band['rate'];
            $bandWidth = $max - $previousMax;
            $taxableInBand = min($remaining, $bandWidth);

            $tax += $taxableInBand * $rate;
            $remaining -= $taxableInBand;

            if ($remaining <= 0) {
                break;
            }

            $previousMax = $max;
        }

        $relief = (float) config('payroll.personal_relief');

        return round(max(0, $tax - $relief), 2);
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip — {{ $line->employee->fullName() }} — {{ $line->run->period->label() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { .no-print { display: none; } }</style>
</head>
<body class="bg-gray-100 p-6 text-sm text-gray-800">
    <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-xl font-bold">DayByDay Automotive</h1>
                <p class="text-gray-500">Payslip — {{ $line->run->period->label() }}</p>
            </div>
            <button onclick="window.print()" class="no-print px-3 py-1.5 bg-orange-500 text-white rounded text-xs">Print</button>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
            <div>
                <p class="text-gray-400 text-xs uppercase">Employee</p>
                <p class="font-semibold">{{ $line->employee->fullName() }}</p>
                <p>{{ $line->employee->employee_number }}</p>
                <p>{{ $line->employee->job_title }}</p>
            </div>
            <div class="text-right">
                <p class="text-gray-400 text-xs uppercase">Run</p>
                <p>{{ $line->run->run_number }}</p>
                <p class="text-gray-500">{{ $line->run->period->start_date->format('d M') }} – {{ $line->run->period->end_date->format('d M Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <h2 class="font-semibold mb-2 text-gray-700">Earnings</h2>
                <table class="w-full">
                    <tr><td class="py-1">Basic Salary</td><td class="text-right">KES {{ number_format($line->basic_salary, 2) }}</td></tr>
                    <tr><td class="py-1">Housing Allowance</td><td class="text-right">KES {{ number_format($line->housing_allowance, 2) }}</td></tr>
                    <tr><td class="py-1">Transport Allowance</td><td class="text-right">KES {{ number_format($line->transport_allowance, 2) }}</td></tr>
                    <tr><td class="py-1">Other Allowance</td><td class="text-right">KES {{ number_format($line->other_allowance, 2) }}</td></tr>
                    <tr class="font-semibold border-t"><td class="py-2">Gross Pay</td><td class="text-right py-2">KES {{ number_format($line->gross_pay, 2) }}</td></tr>
                </table>
            </div>
            <div>
                <h2 class="font-semibold mb-2 text-gray-700">Deductions</h2>
                <table class="w-full">
                    <tr><td class="py-1">NSSF (Employee)</td><td class="text-right">KES {{ number_format($line->nssf_employee, 2) }}</td></tr>
                    <tr><td class="py-1">SHIF</td><td class="text-right">KES {{ number_format($line->shif, 2) }}</td></tr>
                    <tr><td class="py-1">Housing Levy</td><td class="text-right">KES {{ number_format($line->housing_levy_employee, 2) }}</td></tr>
                    <tr><td class="py-1">PAYE</td><td class="text-right">KES {{ number_format($line->paye, 2) }}</td></tr>
                    <tr class="font-semibold border-t"><td class="py-2">Total Deductions</td><td class="text-right py-2">KES {{ number_format($line->total_deductions, 2) }}</td></tr>
                </table>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t flex justify-between items-center">
            <span class="text-lg font-bold">Net Pay</span>
            <span class="text-2xl font-bold text-emerald-600">KES {{ number_format($line->net_pay, 2) }}</span>
        </div>

        <p class="mt-8 text-xs text-gray-400 text-center">Statutory rates from config/payroll.php — verify with your accountant before official use.</p>
    </div>
</body>
</html>

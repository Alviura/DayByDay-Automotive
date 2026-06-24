<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $run->run_number }} — Payroll Register</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 24px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { font-weight: bold; background: #fafafa; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom:12px;padding:6px 12px;">Print</button>
    <h1>Payroll Register — {{ $run->period->label() }}</h1>
    <p class="meta">
        Run: {{ $run->run_number }} ·
        {{ $run->period->start_date->format('d M Y') }} – {{ $run->period->end_date->format('d M Y') }} ·
        Status: {{ ucfirst($run->status) }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Job Title</th>
                <th class="num">Gross</th>
                <th class="num">NSSF</th>
                <th class="num">SHIF</th>
                <th class="num">H. Levy</th>
                <th class="num">PAYE</th>
                <th class="num">Deductions</th>
                <th class="num">Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($run->lines as $line)
                <tr>
                    <td>
                        {{ $line->employee->fullName() }}<br>
                        <small style="color:#888">{{ $line->employee->employee_number }}</small>
                    </td>
                    <td>{{ $line->employee->job_title }}</td>
                    <td class="num">{{ number_format($line->gross_pay, 2) }}</td>
                    <td class="num">{{ number_format($line->nssf_employee, 2) }}</td>
                    <td class="num">{{ number_format($line->shif, 2) }}</td>
                    <td class="num">{{ number_format($line->housing_levy_employee, 2) }}</td>
                    <td class="num">{{ number_format($line->paye, 2) }}</td>
                    <td class="num">{{ number_format($line->total_deductions, 2) }}</td>
                    <td class="num">{{ number_format($line->net_pay, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTALS</td>
                <td class="num">{{ number_format($run->total_gross, 2) }}</td>
                <td colspan="4"></td>
                <td class="num">{{ number_format($run->total_deductions, 2) }}</td>
                <td class="num">{{ number_format($run->total_net, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <p class="meta" style="margin-top:16px">Employer statutory cost: KES {{ number_format($run->total_employer_cost, 2) }}</p>
</body>
</html>

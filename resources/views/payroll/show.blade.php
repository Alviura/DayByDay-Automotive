<x-app-layout :title="'Payroll — ' . $period->label()">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    @php $run = $period->latestRun; @endphp

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-money-check-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">{{ $period->label() }}</h1>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $period->start_date->format('d M') }} – {{ $period->end_date->format('d M Y') }} · <span class="capitalize">{{ $period->status }}</span></p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('payroll.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> All Periods</a>
                @can('payroll.run')
                    @unless($period->isLocked())
                        <form method="POST" action="{{ route('payroll.periods.generate', $period) }}"
                              data-confirm="Generate payroll for all active employees?"
                              data-confirm-variant="warning">
                            @csrf
                            <button type="submit" class="mi-btn-orange">
                                <i class="fas fa-calculator text-xs"></i> {{ $run ? 'Regenerate' : 'Generate' }} Payroll
                            </button>
                        </form>
                    @endunless
                @endcan
                @if ($run && $run->isEditable())
                    @can('payroll.lock')
                        <form method="POST" action="{{ route('payroll.runs.lock', $run) }}"
                              data-confirm="Lock this payroll run?"
                              data-confirm-variant="warning">
                            @csrf
                            <button type="submit" class="mi-btn-ghost">Lock Run</button>
                        </form>
                    @endcan
                @endif
                @if ($run && $run->status === 'locked')
                    @can('payroll.lock')
                        <form method="POST" action="{{ route('payroll.runs.mark-paid', $run) }}"
                              data-confirm="Mark payroll as paid?"
                              data-confirm-variant="warning">
                            @csrf
                            <button type="submit" class="mi-btn-orange">Mark Paid</button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        @if ($run)
            <div class="mi-kpi-row">
                <div class="mi-kpi mi-kpi-purple">
                    <div><p class="mi-kpi-label">Gross</p><p class="mi-kpi-value">KES {{ number_format($run->total_gross, 0) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-amber">
                    <div><p class="mi-kpi-label">Deductions</p><p class="mi-kpi-value">KES {{ number_format($run->total_deductions, 0) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-minus-circle"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-green">
                    <div><p class="mi-kpi-label">Net Pay</p><p class="mi-kpi-value">KES {{ number_format($run->total_net, 0) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-wallet"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-orange">
                    <div><p class="mi-kpi-label">Employer Cost</p><p class="mi-kpi-value orange">KES {{ number_format($run->total_employer_cost, 0) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-building"></i></div>
                </div>
            </div>

            <div class="mi-card overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3">
                    <span class="font-semibold text-gray-800">Run {{ $run->run_number }}</span>
                    <div class="flex flex-wrap items-center gap-2">
                        @can('payroll.export')
                            <a href="{{ route('payroll.runs.export', [$run, 'register']) }}" class="mi-btn-ghost text-xs">
                                <i class="fas fa-file-csv"></i> Register CSV
                            </a>
                            <a href="{{ route('payroll.runs.export', [$run, 'bank']) }}" class="mi-btn-ghost text-xs">
                                <i class="fas fa-building-columns"></i> Bank CSV
                            </a>
                            <a href="{{ route('payroll.runs.export', [$run, 'print']) }}" target="_blank" class="mi-btn-ghost text-xs">
                                <i class="fas fa-print"></i> Print Register
                            </a>
                        @endcan
                        <span class="text-sm text-gray-500 capitalize">{{ $run->status }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="mi-table text-sm">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Gross</th>
                                <th>NSSF</th>
                                <th>SHIF</th>
                                <th>H. Levy</th>
                                <th>PAYE</th>
                                <th>Net</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($run->lines as $line)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $line->employee->fullName() }}</div>
                                        <div class="text-xs text-gray-400">{{ $line->employee->employee_number }}</div>
                                    </td>
                                    <td>{{ number_format($line->gross_pay, 2) }}</td>
                                    <td>{{ number_format($line->nssf_employee, 2) }}</td>
                                    <td>{{ number_format($line->shif, 2) }}</td>
                                    <td>{{ number_format($line->housing_levy_employee, 2) }}</td>
                                    <td>{{ number_format($line->paye, 2) }}</td>
                                    <td class="font-medium">{{ number_format($line->net_pay, 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('payroll.payslip', $line) }}" class="mi-btn-ghost text-xs" target="_blank">Payslip</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mi-card p-8 text-center text-gray-400">
                <i class="fas fa-calculator text-3xl mb-3"></i>
                <p>No payroll run yet. Generate payroll to calculate salaries and statutory deductions.</p>
            </div>
        @endif
    </div>
</x-app-layout>

<x-app-layout title="Payroll">
    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-money-check-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Monthly Payroll</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Salary payroll with Kenya statutory deductions (PAYE, NSSF, SHIF, Housing Levy).</p>
                </div>
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div><p class="mi-kpi-label">On Payroll</p><p class="mi-kpi-value">{{ $stats['employees'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div><p class="mi-kpi-label">Periods</p><p class="mi-kpi-value">{{ $stats['periods'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div><p class="mi-kpi-label">Pending</p><p class="mi-kpi-value">{{ $stats['pending'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div><p class="mi-kpi-label">Paid</p><p class="mi-kpi-value orange">{{ $stats['paid'] }}</p></div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>

        @can('payroll.run')
            <div class="mi-card p-5">
                <h2 class="font-semibold text-gray-800 mb-3">New Payroll Period</h2>
                <form method="POST" action="{{ route('payroll.periods.store') }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div>
                        <label class="mi-field-label">Year</label>
                        <input type="number" name="year" value="{{ old('year', now()->year) }}" class="mi-input w-28" min="2020" max="2100" required>
                    </div>
                    <div>
                        <label class="mi-field-label">Month</label>
                        <select name="month" class="mi-select" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected(old('month', now()->month) == $m)>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="mi-btn-orange">Create Period</button>
                </form>
                <x-input-error :messages="$errors->get('year')" />
                <x-input-error :messages="$errors->get('month')" />
            </div>
        @endcan

        <div class="mi-card overflow-hidden">
            <table class="mi-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Latest Run</th>
                        <th>Net Pay</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td class="font-medium">{{ $period->label() }}</td>
                            <td><span class="mi-cat-badge capitalize">{{ $period->status }}</span></td>
                            <td>{{ $period->latestRun?->run_number ?? '—' }}</td>
                            <td>
                                @if ($period->latestRun)
                                    KES {{ number_format($period->latestRun->total_net, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('payroll.periods.show', $period) }}" class="mi-btn-ghost text-xs">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-8 text-gray-400">No payroll periods yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($periods->hasPages())
                <div class="p-4 border-t">{{ $periods->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

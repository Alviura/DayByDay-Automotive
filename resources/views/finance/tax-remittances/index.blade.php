<x-app-layout title="VAT Remittance">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">VAT Remittance</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Track VAT collected from sales and remittance to KRA.</p>
                </div>
            </div>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'vat'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Collected {{ $year }}</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($stats['collected_ytd'], 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-arrow-trend-up"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Remitted {{ $year }}</p>
                    <p class="mi-kpi-value fin-amt">{{ number_format($stats['remitted_ytd'], 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-paper-plane"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Overdue</p>
                    <p class="mi-kpi-value">{{ $stats['due'] }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Paid Periods</p>
                    <p class="mi-kpi-value">{{ $stats['paid'] }}/12</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-check"></i></div>
            </div>
        </div>

        <div class="mi-card p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="mi-field-label">Year</label>
                    <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="mi-input w-28">
                </div>
                <button type="submit" class="mi-btn-orange">View Year</button>
            </form>
        </div>

        <div class="fin-doc-card">
            <table class="mi-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Due Date</th>
                        <th class="text-right">VAT Collected</th>
                        <th class="text-right">Remitted</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($remittances as $remittance)
                        <tr class="fin-index-row" onclick="window.location='{{ route('tax-remittances.show', $remittance) }}'">
                            <td class="font-medium">{{ $remittance->periodLabel() }}</td>
                            <td>{{ $remittance->due_date?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right fin-amt">{{ number_format($remittance->tax_collected, 2) }}</td>
                            <td class="text-right fin-amt">{{ number_format($remittance->amount_remitted, 2) }}</td>
                            <td class="text-right fin-amt">{{ number_format($remittance->balanceDue(), 2) }}</td>
                            <td>
                                <span class="fin-status-pill fin-status-{{ $remittance->status }}">{{ $remittance->statusLabel() }}</span>
                            </td>
                            <td class="text-right text-gray-400"><i class="fas fa-chevron-right text-xs"></i></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

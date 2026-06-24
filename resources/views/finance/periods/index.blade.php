<x-app-layout title="Period Close">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-lock"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Accounting Periods</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Close periods to block new journal postings. Current: {{ $stats['current'] }}.</p>
                </div>
            </div>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'periods'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Open Periods</p>
                    <p class="mi-kpi-value">{{ $stats['open'] }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-lock-open"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Closed Periods</p>
                    <p class="mi-kpi-value">{{ $stats['closed'] }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-lock"></i></div>
            </div>
        </div>

        <div class="fin-doc-card">
            <table class="mi-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Closed</th>
                        <th>Closed By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($periods as $period)
                        <tr>
                            <td class="font-medium">{{ $period->periodLabel() }}</td>
                            <td>
                                <span class="fin-status-pill fin-status-{{ $period->status === 'closed' ? 'reconciled' : 'open' }}">
                                    {{ $period->isClosed() ? 'Closed' : 'Open' }}
                                </span>
                            </td>
                            <td>{{ $period->closed_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $period->closer?->name ?? '—' }}</td>
                            <td class="text-right">
                                @can('finance.manage')
                                    @if ($period->isClosed())
                                        <form method="POST" action="{{ route('financial-periods.reopen') }}" class="inline"
                                            onsubmit="return confirm('Reopen {{ $period->periodLabel() }}? New postings will be allowed.')">
                                            @csrf
                                            <input type="hidden" name="period_year" value="{{ $period->period_year }}">
                                            <input type="hidden" name="period_month" value="{{ $period->period_month }}">
                                            <button type="submit" class="mi-btn-ghost text-sm">Reopen</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('financial-periods.close') }}" class="inline"
                                            onsubmit="return confirm('Close {{ $period->periodLabel() }}? No new journals can be posted to this period.')">
                                            @csrf
                                            <input type="hidden" name="period_year" value="{{ $period->period_year }}">
                                            <input type="hidden" name="period_month" value="{{ $period->period_month }}">
                                            <button type="submit" class="mi-btn-orange text-sm">Close Period</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="fin-banner fin-banner-balanced no-print">
            <i class="fas fa-circle-info"></i>
            <span>Closing a period blocks automated and manual journal entries dated in that month. Reopen if corrections are needed.</span>
        </div>
    </div>
</x-app-layout>

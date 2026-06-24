<x-app-layout title="Bank Reconciliation">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-building-columns"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Bank Reconciliation</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Match GL cash/bank balances to bank statements.</p>
                </div>
            </div>
            @can('finance.manage')
                <a href="{{ route('bank-reconciliations.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> New Reconciliation
                </a>
            @endcan
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'bank-recon'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Draft</p>
                    <p class="mi-kpi-value">{{ $stats['draft'] }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-pen"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Reconciled</p>
                    <p class="mi-kpi-value">{{ $stats['reconciled'] }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">This Month</p>
                    <p class="mi-kpi-value">{{ $stats['this_month'] }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
        </div>

        <div class="fin-doc-card">
            @if ($recons->isEmpty())
                <div class="fin-empty">
                    <div class="fin-empty-icon"><i class="fas fa-building-columns"></i></div>
                    <p class="font-semibold text-gray-700">No reconciliations yet</p>
                    <p class="text-sm text-gray-500 mt-1">Create a reconciliation when you receive a bank statement.</p>
                </div>
            @else
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Statement Date</th>
                            <th class="text-right">Statement Bal.</th>
                            <th class="text-right">Book Bal.</th>
                            <th>Status</th>
                            <th>Reconciled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recons as $recon)
                            <tr class="fin-index-row" onclick="window.location='{{ route('bank-reconciliations.show', $recon) }}'">
                                <td>
                                    <p class="font-medium">{{ $recon->account->name }}</p>
                                    <p class="fin-mono text-xs text-gray-500">{{ $recon->account->code }}</p>
                                </td>
                                <td>{{ $recon->statement_date->format('d M Y') }}</td>
                                <td class="text-right fin-amt">{{ number_format($recon->statement_balance, 2) }}</td>
                                <td class="text-right fin-amt">{{ $recon->book_balance !== null ? number_format($recon->book_balance, 2) : '—' }}</td>
                                <td><span class="fin-status-pill fin-status-{{ $recon->status }}">{{ ucfirst($recon->status) }}</span></td>
                                <td>{{ $recon->reconciled_at?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $recons->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

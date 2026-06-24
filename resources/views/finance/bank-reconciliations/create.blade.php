<x-app-layout title="New Bank Reconciliation">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5 max-w-2xl">

        <div class="flex items-start gap-3">
            <div class="mi-page-icon"><i class="fas fa-building-columns"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">New Bank Reconciliation</h1>
                <p class="mt-0.5 text-sm text-gray-500">Enter statement details to start matching transactions.</p>
            </div>
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'bank-recon'])

        <form method="POST" action="{{ route('bank-reconciliations.store') }}" class="fin-doc-card p-5 space-y-4">
            @csrf

            <div>
                <label class="mi-field-label">Bank / Cash Account</label>
                <select name="chart_of_account_id" class="mi-input w-full" required
                    onchange="window.location='{{ route('bank-reconciliations.create') }}?account_id='+this.value+'&statement_date='+document.getElementById('statement_date').value">
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected($selectedAccount?->id === $account->id)>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mi-field-label">Statement Date</label>
                <input type="date" id="statement_date" name="statement_date" value="{{ $statementDate->toDateString() }}" class="mi-input w-full" required
                    onchange="window.location='{{ route('bank-reconciliations.create') }}?account_id={{ $selectedAccount?->id }}&statement_date='+this.value">
            </div>

            @if ($selectedAccount)
                <div class="fin-banner fin-banner-balanced">
                    <i class="fas fa-book"></i>
                    <span>Book balance as of {{ $statementDate->format('d M Y') }}: <strong>KES {{ number_format($bookBalance, 2) }}</strong></span>
                </div>
            @endif

            <div>
                <label class="mi-field-label">Statement Ending Balance (KES)</label>
                <input type="number" name="statement_balance" step="0.01" class="mi-input w-full" required>
            </div>

            <div>
                <label class="mi-field-label">Notes</label>
                <textarea name="notes" rows="3" class="mi-input w-full"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="mi-btn-orange">Continue</button>
                <a href="{{ route('bank-reconciliations.index') }}" class="mi-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>

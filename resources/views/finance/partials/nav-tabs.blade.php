@php
    $active = $active ?? '';
@endphp
<nav class="fin-nav no-print" aria-label="Finance modules">
    <a href="{{ route('chart-of-accounts.index') }}" class="fin-nav-link {{ $active === 'coa' ? 'active' : '' }}">
        <i class="fas fa-sitemap"></i> Chart of Accounts
    </a>
    <a href="{{ route('journal-entries.index') }}" class="fin-nav-link {{ $active === 'ledger' ? 'active' : '' }}">
        <i class="fas fa-book"></i> Journal Ledger
    </a>
    <a href="{{ route('trial-balance.index') }}" class="fin-nav-link {{ $active === 'trial-balance' ? 'active' : '' }}">
        <i class="fas fa-scale-balanced"></i> Trial Balance
    </a>
    <a href="{{ route('financial-statements.index') }}" class="fin-nav-link {{ $active === 'statements' ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i> Statements
    </a>
    <a href="{{ route('tax-remittances.index') }}" class="fin-nav-link {{ $active === 'vat' ? 'active' : '' }}">
        <i class="fas fa-file-invoice-dollar"></i> VAT
    </a>
    <a href="{{ route('bank-reconciliations.index') }}" class="fin-nav-link {{ $active === 'bank-recon' ? 'active' : '' }}">
        <i class="fas fa-building-columns"></i> Bank Recon
    </a>
    <a href="{{ route('financial-periods.index') }}" class="fin-nav-link {{ $active === 'periods' ? 'active' : '' }}">
        <i class="fas fa-lock"></i> Periods
    </a>
    @can('finance.journal')
        <a href="{{ route('journal-entries.create') }}" class="fin-nav-link ml-auto {{ $active === 'create-journal' ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Manual Journal
        </a>
    @endcan
</nav>

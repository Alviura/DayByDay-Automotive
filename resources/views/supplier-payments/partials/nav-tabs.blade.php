@php
    $active = $active ?? 'index';
@endphp
<nav class="sp-nav no-print" aria-label="Supplier payments">
    <a href="{{ route('supplier-payments.index') }}" class="sp-nav-link {{ $active === 'index' ? 'active' : '' }}">
        <i class="fas fa-list"></i> All Payments
    </a>
    @can('supplier_payments.manage')
        <a href="{{ route('supplier-payments.create') }}" class="sp-nav-link {{ $active === 'create' ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Record Payment
        </a>
    @endcan
    @can('procurement.view')
        <a href="{{ route('goods-receipts.index') }}" class="sp-nav-link ml-auto">
            <i class="fas fa-truck-ramp-box"></i> Goods Receipts
        </a>
    @endcan
</nav>

@props(['customerAccount', 'outstanding' => 0, 'uninvoicedCount' => 0])

<section class="mi-guide-section">
    <h3 class="mi-guide-section-title">
        <i class="fas fa-route"></i> Billing workflow
    </h3>
    <ul class="mi-guide-list">
        <li><strong>1. Credit sales</strong><span>Parts issued on account via Order Entry → Cash desk.</span></li>
        <li><strong>2. Generate invoice</strong><span>Roll up uninvoiced sales at month-end.</span></li>
        <li><strong>3. Record payment</strong><span>Apply lump payment when the fleet settles.</span></li>
    </ul>
</section>

@if ($uninvoicedCount > 0)
    @can('customer_invoices.manage')
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-file-invoice"></i>
            <p>{{ $uninvoicedCount }} uninvoiced sale{{ $uninvoicedCount === 1 ? '' : 's' }} ready to bill.</p>
            <a href="{{ route('customer-invoices.create', ['account_id' => $customerAccount->id]) }}" class="mi-btn-orange w-full justify-center mt-3">
                <i class="fas fa-file-invoice text-xs"></i> Generate Invoice
            </a>
        </div>
    @endcan
@elseif ($outstanding <= 0)
    <div class="mi-guide-note mi-guide-note-blue">
        <i class="fas fa-circle-check"></i>
        <p>No outstanding balance — account is fully settled.</p>
    </div>
@endif

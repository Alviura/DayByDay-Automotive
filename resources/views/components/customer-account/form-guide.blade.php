@props(['isEdit' => false, 'customerAccount' => null])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'setting up') . ' a fleet account'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-bus"></i> What is a fleet account?
        </h3>
        <p class="mi-guide-text">
            Fleet accounts are credit customers — typically PSV operators — who take parts on account during the month and settle with a lump payment against a monthly invoice.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Credit workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Order Entry</strong><span>Switch to Fleet mode, select this account, and enter the vehicle plate.</span></li>
            <li><strong>Cash desk</strong><span>Shop manager issues the sale on account — no payment at the till.</span></li>
            <li><strong>Month-end</strong><span>Generate an invoice from uninvoiced sales on this account.</span></li>
            <li><strong>Payment</strong><span>Record the lump payment against the invoice when received.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-list-check"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Account name</strong><span>Shown in dropdowns and on statements (e.g. Jane's PSV Fleet).</span></li>
            <li><strong>Contact & phone</strong><span>For billing follow-up and invoice delivery.</span></li>
            <li><strong>Billing terms</strong><span>How often you invoice — monthly is typical for fleets.</span></li>
            <li><strong>Credit limit</strong><span>Optional cap on total outstanding before new sales are blocked.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Always capture the vehicle plate on fleet orders for audit trails.</li>
            <li><i class="fas fa-check"></i> Set a credit limit for new accounts until payment history is established.</li>
            <li><i class="fas fa-check"></i> Deactivate accounts that are on hold — don't delete accounts with sales history.</li>
            @if ($isEdit && $customerAccount?->sales()->exists())
                <li><i class="fas fa-check"></i> This account has sales history — changes won't affect past transactions.</li>
            @endif
        </ul>
    </section>

    @if ($isEdit)
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Deactivating hides this account from Order Entry but keeps all invoices and sales intact.</p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-arrow-right"></i>
            <p>After creating, the account appears in Order Entry when staff switch to Fleet mode.</p>
        </div>
    @endif
</x-module.form-guide>

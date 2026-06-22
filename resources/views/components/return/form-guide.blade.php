@props(['type' => 'customer'])

<x-module.form-guide :subtitle="$type === 'customer' ? 'Linked to a completed sale' : 'Return stock from warehouse to supplier'">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-rotate-left"></i> Workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Draft</strong><span>Build lines and review details.</span></li>
            <li><strong>Awaiting approval</strong><span>Submitted to the Approvals inbox.</span></li>
            <li><strong>Completed</strong><span>
                @if ($type === 'customer')
                    Refund recorded; good items restocked to the shop.
                @else
                    Stock removed from the warehouse ledger.
                @endif
            </span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title"><i class="fas fa-lightbulb"></i> Tips</h3>
        <ul class="mi-guide-tips">
            @if ($type === 'customer')
                <li><i class="fas fa-check"></i> Search by receipt #, fleet account, or vehicle plate.</li>
                <li><i class="fas fa-check"></i> Return qty cannot exceed what is still returnable on the sale line.</li>
                <li><i class="fas fa-check"></i> Fleet credit refunds reduce the account outstanding balance.</li>
            @else
                <li><i class="fas fa-check"></i> Available warehouse qty is checked before you submit.</li>
                <li><i class="fas fa-check"></i> Only stock physically at the warehouse can be returned.</li>
            @endif
            <li><i class="fas fa-check"></i> Rejected returns can be revised and resubmitted from draft.</li>
        </ul>
    </section>
</x-module.form-guide>

@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'setting up') . ' a supplier'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-truck"></i> What is a supplier?
        </h3>
        <p class="mi-guide-text">
            A supplier is a vendor you purchase parts from. Supplier records feed into procurement folders and purchase orders.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-list-check"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Name</strong><span>Legal or trading name shown on POs and reports.</span></li>
            <li><strong>Code</strong><span>Unique short ID (e.g. SUP-AGL) for quick lookup.</span></li>
            <li><strong>Contact</strong><span>Primary person, phone, and email for orders.</span></li>
            <li><strong>Currency</strong><span>Default currency for quotes and purchase orders.</span></li>
            <li><strong>Lead time</strong><span>Typical days from order to delivery — used for planning.</span></li>
            <li><strong>Rating</strong><span>Internal quality score from 0 to 5 (optional).</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use a <code>SUP-</code> prefix for all supplier codes.</li>
            <li><i class="fas fa-check"></i> Set currency to match how the supplier invoices you.</li>
            <li><i class="fas fa-check"></i> Deactivate suppliers you no longer order from instead of deleting.</li>
        </ul>
    </section>

    @if ($isEdit)
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Saving changes updates this supplier across linked procurement records.</p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-arrow-right"></i>
            <p>After creating, use this supplier when raising procurement folders and purchase orders.</p>
        </div>
    @endif
</x-module.form-guide>

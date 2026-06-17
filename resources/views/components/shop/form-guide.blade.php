@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'setting up') . ' a shop'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-store"></i> What is a shop?
        </h3>
        <p class="mi-guide-text">
            A shop is a retail location where sales are made and stock is held on the floor. Each shop has a unique code used across POS, transfers, and reporting.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-list-check"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li>
                <strong>Name</strong>
                <span>Display label shown in lists and dropdowns (e.g. Downtown Auto Parts).</span>
            </li>
            <li>
                <strong>Code</strong>
                <span>Short, unique ID — uppercase letters, numbers, and dashes (e.g. SH-DTOWN).</span>
            </li>
            <li>
                <strong>Address</strong>
                <span>Physical storefront location. Optional but recommended for receipts and transfers.</span>
            </li>
            <li>
                <strong>Phone</strong>
                <span>Contact number for customers and internal coordination. Optional.</span>
            </li>
            <li>
                <strong>Active</strong>
                <span>Inactive shops are hidden from new assignments but existing sales and stock records are kept.</span>
            </li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use a consistent code prefix like <code>SH-</code> for all shops.</li>
            <li><i class="fas fa-check"></i> Keep codes short — they appear on receipts and transfer documents.</li>
            @if ($isEdit)
                <li><i class="fas fa-check"></i> Changing the code may confuse staff — update carefully.</li>
            @else
                <li><i class="fas fa-check"></i> Choose a code that won't need changing later — codes are referenced in sales data.</li>
            @endif
            <li><i class="fas fa-check"></i> Deactivate instead of deleting when a shop is temporarily closed.</li>
        </ul>
    </section>

    @if ($isEdit)
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Saving changes updates this shop immediately across the system.</p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-arrow-right"></i>
            <p>After creating, you can assign staff and receive stock transfers to this shop.</p>
        </div>
    @endif
</x-module.form-guide>

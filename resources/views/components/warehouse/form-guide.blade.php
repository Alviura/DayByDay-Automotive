@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'setting up') . ' a warehouse'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-warehouse"></i> What is a warehouse?
        </h3>
        <p class="mi-guide-text">
            A warehouse is a storage location where inventory is held. Each warehouse has a unique code used across stock transfers, receiving, and reporting.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-list-check"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li>
                <strong>Name</strong>
                <span>Display label shown in lists and dropdowns (e.g. Main Warehouse).</span>
            </li>
            <li>
                <strong>Code</strong>
                <span>Short, unique ID — uppercase letters, numbers, and dashes (e.g. WH-MAIN).</span>
            </li>
            <li>
                <strong>Address</strong>
                <span>Physical location for deliveries and internal reference. Optional but recommended.</span>
            </li>
            <li>
                <strong>Phone</strong>
                <span>Contact number for coordination. Optional.</span>
            </li>
            <li>
                <strong>Active</strong>
                <span>Inactive warehouses are hidden from new assignments but existing stock records are kept.</span>
            </li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use a consistent code prefix like <code>WH-</code> for all warehouses.</li>
            <li><i class="fas fa-check"></i> Keep codes short — they appear in reports and stock labels.</li>
            @if ($isEdit)
                <li><i class="fas fa-check"></i> Changing the code may affect how staff recognise this location — update carefully.</li>
            @else
                <li><i class="fas fa-check"></i> Choose a code that won't need changing later — codes are referenced in stock data.</li>
            @endif
            <li><i class="fas fa-check"></i> Deactivate instead of deleting when a location is no longer in use.</li>
        </ul>
    </section>

    @if ($isEdit)
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Saving changes updates this warehouse immediately across the system.</p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-arrow-right"></i>
            <p>After creating, you can assign users and link stock balances to this warehouse.</p>
        </div>
    @endif
</x-module.form-guide>

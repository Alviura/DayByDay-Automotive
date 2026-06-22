<x-module.form-guide subtitle="Warehouse to shop distribution">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-right-left"></i> One module, full journey
        </h3>
        <p class="mi-guide-text">
            Each transfer record covers the complete lifecycle — from draft request through dispatch to shop receipt. No separate screens for in-transit stock.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Draft</strong><span>Create lines with live source availability.</span></li>
            <li><strong>Pending</strong><span>Awaiting approval.</span></li>
            <li><strong>Approved</strong><span>Stock reserved at source.</span></li>
            <li><strong>In transit</strong><span>Dispatched — receive at destination.</span></li>
            <li><strong>Completed</strong><span>Inventory updated both sides.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Tips
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> After GRN from quotation series, transfer warehouse stock to shops.</li>
            <li><i class="fas fa-check"></i> Filter by <strong>In Transit</strong> to see what needs receiving.</li>
            <li><i class="fas fa-check"></i> Available qty excludes reservations from sales and other approved transfers.</li>
        </ul>
    </section>

    <div class="mi-guide-note mi-guide-note-blue">
        <i class="fas fa-boxes-stacked"></i>
        <p>View balances in <a href="{{ route('inventory.index') }}" class="font-semibold underline">Inventory</a> before creating large transfers.</p>
    </div>
</x-module.form-guide>

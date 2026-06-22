<x-module.form-guide subtitle="Warehouse to shop distribution">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-right-left"></i> End-to-end transfer
        </h3>
        <p class="mi-guide-text">
            One record tracks the full journey: request → approval → dispatch → receipt. After goods are received via GRN into a warehouse, transfer stock to shops for floor sales.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Draft</strong><span>Create lines — available qty is checked at source.</span></li>
            <li><strong>Pending</strong><span>Submitted for approval.</span></li>
            <li><strong>Approved</strong><span>Source stock is reserved.</span></li>
            <li><strong>In transit</strong><span>Dispatched — stock left the source.</span></li>
            <li><strong>Completed</strong><span>Destination confirmed receipt.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Tips
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use <strong>Warehouse → Shop</strong> after monthly GRNs from quotation series.</li>
            <li><i class="fas fa-check"></i> Available qty excludes stock already reserved by held sales or approved transfers.</li>
            <li><i class="fas fa-check"></i> Receive at the destination shop to post stock to the floor ledger.</li>
        </ul>
    </section>

    <div class="mi-guide-note mi-guide-note-blue">
        <i class="fas fa-boxes-stacked"></i>
        <p>Check <a href="{{ route('inventory.index') }}" class="font-semibold underline">Inventory</a> for warehouse balances before requesting large quantities.</p>
    </div>
</x-module.form-guide>

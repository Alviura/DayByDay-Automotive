@props(['forShopManager' => false, 'forWarehouseManager' => false])

<x-module.form-guide :subtitle="$forShopManager ? 'Shop stock movements' : ($forWarehouseManager ? 'Warehouse distribution' : 'Warehouse to shop distribution')">
    @if ($forWarehouseManager)
        <section class="mi-guide-section mi-guide-section-first">
            <h3 class="mi-guide-section-title">
                <i class="fas fa-warehouse"></i> What you can do
            </h3>
            <ul class="mi-guide-list">
                <li><strong>Warehouse → Shop</strong><span>Distribute stock from your warehouse to shops.</span></li>
                <li><strong>Receive returns</strong><span>Confirm shop-to-warehouse transfers when stock is returned.</span></li>
            </ul>
            <p class="mi-guide-text mt-3">
                Dispatch approved transfers to send stock out. Shop-initiated returns appear here for receipt after approval.
            </p>
        </section>
    @elseif ($forShopManager)
        <section class="mi-guide-section mi-guide-section-first">
            <h3 class="mi-guide-section-title">
                <i class="fas fa-store"></i> What you can request
            </h3>
            <ul class="mi-guide-list">
                <li><strong>Shop → Shop</strong><span>Move stock to another branch from your shop.</span></li>
                <li><strong>Shop → Warehouse</strong><span>Return excess or slow-moving stock to the warehouse.</span></li>
            </ul>
            <p class="mi-guide-text mt-3">
                Warehouse distributions <strong>to your shop</strong> are created by administrators. You can track and receive those transfers here.
            </p>
        </section>
    @else
        <section class="mi-guide-section mi-guide-section-first">
            <h3 class="mi-guide-section-title">
                <i class="fas fa-right-left"></i> End-to-end transfer
            </h3>
            <p class="mi-guide-text">
                One record tracks the full journey: request → approval → dispatch → receipt. After goods are received via GRN into a warehouse, transfer stock to shops for floor sales.
            </p>
        </section>
    @endif

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Draft</strong><span>Create lines — available qty is checked at source.</span></li>
            <li><strong>Pending</strong><span>Submitted for administrator approval.</span></li>
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
            @if ($forShopManager)
                <li><i class="fas fa-check"></i> You can only transfer <strong>from your assigned shop</strong>.</li>
                <li><i class="fas fa-check"></i> Receive inbound warehouse transfers when they arrive at your shop.</li>
            @elseif ($forWarehouseManager)
                <li><i class="fas fa-check"></i> You can only distribute <strong>from your assigned warehouse</strong>.</li>
                <li><i class="fas fa-check"></i> Dispatch after administrator approval.</li>
            @else
                <li><i class="fas fa-check"></i> Use <strong>Warehouse → Shop</strong> after monthly GRNs from quotation series.</li>
                <li><i class="fas fa-check"></i> Use <strong>Shop → Warehouse</strong> when shops return stock centrally.</li>
            @endif
            <li><i class="fas fa-check"></i> Available qty excludes stock already reserved by held sales or approved transfers.</li>
        </ul>
    </section>

    <div class="mi-guide-note mi-guide-note-blue">
        <i class="fas fa-boxes-stacked"></i>
        <p>Check <a href="{{ route('inventory.index') }}" class="font-semibold underline">Inventory</a> for balances before requesting large quantities.</p>
    </div>
</x-module.form-guide>

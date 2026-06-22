<x-module.form-guide subtitle="How stock value is calculated">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-coins"></i> What is this?
        </h3>
        <p class="mi-guide-text">
            Inventory valuation shows the monetary worth of stock on hand at each warehouse and shop, using weighted average cost from the ledger.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-calculator"></i> Formula
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Line value</strong><span>Quantity on hand × average cost at that location.</span></li>
            <li><strong>Location total</strong><span>Sum of all product lines with qty &gt; 0 at the site.</span></li>
            <li><strong>Grand total</strong><span>All locations combined (same SKU in two sites counts twice).</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-layer-group"></i> Reading the table
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Share</strong><span>What % of total inventory value sits at that location.</span></li>
            <li><strong>SKUs</strong><span>Products with on-hand qty at that location only.</span></li>
            <li><strong>Detail</strong><span>Drill into SKU-level qty, cost, and value for one site.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Tips
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Warehouse value usually reflects bulk storage; shop value is floor stock.</li>
            <li><i class="fas fa-check"></i> Average cost updates on goods receipt — valuation moves with purchases.</li>
            <li><i class="fas fa-check"></i> Zero-value locations still appear unless you hide empty sites.</li>
        </ul>
    </section>

    @if ($detailLocation ?? null)
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-map-pin"></i>
            <p>
                Viewing <strong>{{ $detailLocation->name }}</strong> —
                {{ number_format($detail['total_value'] ?? 0, 2) }} KES across
                {{ number_format($detail['sku_count'] ?? 0) }} SKUs.
            </p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-hand-pointer"></i>
            <p>Click a location row to open SKU-level breakdown.</p>
        </div>
    @endif
</x-module.form-guide>

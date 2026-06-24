@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'creating') . ' a product'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-car-side"></i> What is a product?
        </h3>
        <p class="mi-guide-text">
            A product is a sellable part in your catalogue — identified by part number and linked to a product name, category, unit, and vehicle fitment.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-list-check"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Part number</strong><span>Unique code — uppercase recommended (e.g. OIL-FIL-TYT-001).</span></li>
            <li><strong>Product name</strong><span>Lookup from your product names catalogue — used as the display label.</span></li>
            <li><strong>Primary fitment</strong><span>Main make/model this part is listed under.</span></li>
            <li><strong>Additional fitment</strong><span>Optional toggle — expand to pick extra compatible models.</span></li>
            <li><strong>Prices</strong><span>Min and max selling price — POS defaults to max; cost updates on goods receipt.</span></li>
            <li><strong>Supplier ordering</strong><span>How the supplier quotes (piece, pair, or set) and how many stock pieces each order unit contains.</span></li>
            <li><strong>Packaging &amp; CBM</strong><span>Measure the shipping pack (W×L×H in metres). Import freight is charged per CBM packet; for pairs/sets freight is shown per pair/set, not per piece.</span></li>
            <li><strong>Reorder level</strong><span>Triggers low-stock alerts once inventory is live.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use a consistent part-number pattern: TYPE-MAKE-MODEL-SEQ.</li>
            <li><i class="fas fa-check"></i> Set a realistic min–max range for negotiated counter sales.</li>
            <li><i class="fas fa-check"></i> Deactivate discontinued parts instead of deleting them.</li>
        </ul>
    </section>

    @if ($isEdit)
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Price changes here do not affect historical sales or ledger entries.</p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-arrow-right"></i>
            <p>After creating, stock can be received via procurement once those modules are built.</p>
        </div>
    @endif
</x-module.form-guide>

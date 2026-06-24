<x-module.form-guide subtitle="Request stock for your shop">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-store"></i> What you can request
        </h3>
        <ul class="mi-guide-list">
            <li><strong>From warehouse</strong><span>Ask the warehouse to send stock to your shop.</span></li>
            <li><strong>From another shop</strong><span>Request stock from a sister branch.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Draft</strong><span>Build your request lines.</span></li>
            <li><strong>Submitted</strong><span>Warehouse or source shop manager reviews.</span></li>
            <li><strong>Accepted</strong><span>Reviewer issues a stock transfer for administrator approval.</span></li>
            <li><strong>Fulfilled</strong><span>Stock received at your shop.</span></li>
        </ul>
    </section>

    <div class="mi-guide-note mi-guide-note-blue">
        <i class="fas fa-boxes-stacked"></i>
        <p>Check <a href="{{ route('inventory.index') }}" class="font-semibold underline">Inventory</a> before requesting large quantities.</p>
    </div>
</x-module.form-guide>

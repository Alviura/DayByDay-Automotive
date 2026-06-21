@props(['isEdit' => false])

@push('styles')
<style>
    .qs-guide-steps {
        list-style: none; margin: 0; padding: 0;
        display: flex; flex-direction: column; gap: .65rem;
    }
    .qs-guide-steps li {
        display: flex; align-items: flex-start; gap: .6rem;
        font-size: .76rem; line-height: 1.45;
    }
    .qs-guide-step-num {
        width: 1.35rem; height: 1.35rem; border-radius: 50%;
        background: #fff7ed; border: 1px solid #fed7aa;
        color: #ea580c; font-size: .65rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: .1rem;
    }
    .qs-guide-steps strong { display: block; color: #374151; font-weight: 600; font-size: .78rem; }
    .qs-guide-steps span { color: #9ca3af; }
    .qs-guide-example {
        margin-top: .5rem; padding: .55rem .75rem;
        background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;
    }
    .qs-guide-example code, .qs-guide-code {
        font-size: .72rem; color: #374151; word-break: break-word;
    }
</style>
@endpush

<x-module.form-guide :subtitle="$isEdit ? 'Updating series header details' : 'How quotation series works'">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Three-phase workflow
        </h3>
        <ol class="qs-guide-steps">
            <li>
                <span class="qs-guide-step-num">1</span>
                <div>
                    <strong>Create series</strong>
                    <span>Pick supplier, set type &amp; rates — you are here.</span>
                </div>
            </li>
            <li>
                <span class="qs-guide-step-num">2</span>
                <div>
                    <strong>Quotation draft</strong>
                    <span>Bulk-add products with quantities; export blank-price draft to supplier.</span>
                </div>
            </li>
            <li>
                <span class="qs-guide-step-num">3</span>
                <div>
                    <strong>Order processing</strong>
                    <span>Enter supplier prices, calculate margins, confirm — auto-approved.</span>
                </div>
            </li>
        </ol>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-input-text"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Supplier</strong><span>Required. Auto-fills currency and purchase type from supplier profile.</span></li>
            <li><strong>Description</strong><span>Optional suffix appended to the display title (e.g. product group or vehicle line).</span></li>
            <li><strong>Purchase type</strong><span><em>Local</em> — KES pricing, manual transport per line. <em>Import</em> — foreign currency + CBM transport allocation.</span></li>
            <li><strong>Conversion (R)</strong><span>Exchange rate to KES. Required for import; defaults to 1 for local.</span></li>
            <li><strong>CBM (R)</strong><span>Transport cost per cubic metre (KES). Required before import order processing.</span></li>
            <li><strong>Notes</strong><span>Internal remarks — not shown on supplier export.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-tag"></i> Title format
        </h3>
        <p class="mi-guide-text">
            The system generates a display name automatically:
        </p>
        <div class="qs-guide-example">
            <code>20JUN2026 - SUPPLIER NAME - DESCRIPTION</code>
        </div>
        <p class="mi-guide-text mt-2">
            Internal reference stays as <code class="qs-guide-code">PF-2026-####</code> for PO and audit linkage.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Tips
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Set supplier purchase type correctly in Master Data before creating series.</li>
            <li><i class="fas fa-check"></i> For imports, confirm Conversion(R) and CBM(R) with your freight forwarder early.</li>
            <li><i class="fas fa-check"></i> Use description for quick identification — e.g. <em>NISSAN AIR CLEANERS</em>.</li>
        </ul>
    </section>

    @if ($isEdit)
        <div class="mi-guide-note mi-guide-note-amber">
            <i class="fas fa-triangle-exclamation"></i>
            <p>Header changes are only allowed while the series is in quotation or order draft status.</p>
        </div>
    @else
        <div class="mi-guide-note mi-guide-note-blue">
            <i class="fas fa-arrow-right"></i>
            <p>After creating, you'll land on the series page to bulk-add quotation products.</p>
        </div>
    @endif
</x-module.form-guide>

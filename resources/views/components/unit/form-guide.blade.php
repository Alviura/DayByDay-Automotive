@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'adding') . ' a unit of measure'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-ruler-combined"></i> What is a unit?
        </h3>
        <p class="mi-guide-text">
            Units define how products are counted or sold — per piece, set, litre, kilogram, etc. They appear on stock records, POs, and sales receipts.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use standard abbreviations like <code>PCS</code>, <code>SET</code>, <code>LTR</code>.</li>
            <li><i class="fas fa-check"></i> Keep the list short — only units you actually use in trade.</li>
            <li><i class="fas fa-check"></i> Deactivate obsolete units instead of deleting them.</li>
        </ul>
    </section>
</x-module.form-guide>

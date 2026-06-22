<x-module.form-guide subtitle="Physical count adjustments">
    <section class="mi-guide-section mi-guide-section-first">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-sliders"></i> What is this?
        </h3>
        <p class="mi-guide-text">
            A stock adjustment records the difference between what the system says you have and what you physically counted at a warehouse or shop.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-route"></i> Workflow
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Save draft</strong><span>Capture location, reason, and count lines without affecting stock.</span></li>
            <li><strong>Submit</strong><span>Sends the adjustment for approval when at least one line has a variance.</span></li>
            <li><strong>Approve</strong><span>An authorised user approves the request.</span></li>
            <li><strong>Post</strong><span>Ledger entries are created and on-hand quantities update at the selected location.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-list-check"></i> Field reference
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Location</strong><span>One warehouse or shop per adjustment — all lines apply to that location only.</span></li>
            <li><strong>Reason</strong><span>Why the variance occurred (count, damage, loss, correction).</span></li>
            <li><strong>System qty</strong><span>Current on-hand from the ledger — filled automatically when you pick a product.</span></li>
            <li><strong>Counted qty</strong><span>What you physically counted on the shelf or floor.</span></li>
            <li><strong>Variance</strong><span>Counted minus system. Positive adds stock; negative removes it.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-tag"></i> Reason codes
        </h3>
        <ul class="mi-guide-list">
            <li><strong>Count variance</strong><span>Routine cycle count or stocktake difference.</span></li>
            <li><strong>Damaged</strong><span>Stock written off due to damage.</span></li>
            <li><strong>Lost / missing</strong><span>Shrinkage or items that cannot be located.</span></li>
            <li><strong>Correction</strong><span>Fix a prior posting or data entry error.</span></li>
        </ul>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Tips
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Pick the location first — system qty loads per product at that location.</li>
            <li><i class="fas fa-check"></i> Count one location at a time; do not mix warehouse and shop on the same form.</li>
            <li><i class="fas fa-check"></i> Add notes for the approver when the variance is unusual or large.</li>
            <li><i class="fas fa-check"></i> Lines with zero variance are saved but only non-zero lines post to the ledger.</li>
        </ul>
    </section>

    <div class="mi-guide-note mi-guide-note-amber">
        <i class="fas fa-triangle-exclamation"></i>
        <p>Adjustments require approval before stock changes. Saving a draft does not update inventory.</p>
    </div>

    <div class="mi-guide-note mi-guide-note-blue">
        <i class="fas fa-arrow-right"></i>
        <p>After saving, open the adjustment to review lines and submit for approval.</p>
    </div>
</x-module.form-guide>

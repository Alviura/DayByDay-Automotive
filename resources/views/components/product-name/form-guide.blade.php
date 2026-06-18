@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'adding') . ' a product name'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-tags"></i> What is a product name?
        </h3>
        <p class="mi-guide-text">
            A product name is the generic type of part — like "Oil Filter" or "Brake Pad". It is separate from the full product record which includes brand, part number, and fitment.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use standard industry terms staff and customers understand.</li>
            <li><i class="fas fa-check"></i> Keep names generic — brand details go on the product itself.</li>
            <li><i class="fas fa-check"></i> Avoid duplicates — each name should appear once in the list.</li>
        </ul>
    </section>
</x-module.form-guide>

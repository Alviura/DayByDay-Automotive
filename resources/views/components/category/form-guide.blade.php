@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'adding') . ' a category'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-folder-tree"></i> Category hierarchy
        </h3>
        <p class="mi-guide-text">
            Categories organise your product catalogue in a tree. Top-level groups like "Engine Parts" can have sub-categories like "Filters" and "Belts".
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Keep the tree shallow — two levels is usually enough.</li>
            <li><i class="fas fa-check"></i> Use clear names customers and staff recognise.</li>
            <li><i class="fas fa-check"></i> Move sub-categories before deleting a parent.</li>
        </ul>
    </section>
</x-module.form-guide>

@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'adding') . ' a vehicle make'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-car-side"></i> What is a make?
        </h3>
        <p class="mi-guide-text">
            A vehicle make is the manufacturer or brand (e.g. Toyota, Nissan). Models belong to a make and products are linked to makes for fitment.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Use the official brand name spelling.</li>
            <li><i class="fas fa-check"></i> Add models under this make before linking products.</li>
            <li><i class="fas fa-check"></i> Deactivate makes you no longer stock parts for.</li>
        </ul>
    </section>
</x-module.form-guide>

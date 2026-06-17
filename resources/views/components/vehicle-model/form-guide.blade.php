@props(['isEdit' => false])

<x-module.form-guide :subtitle="'Tips for ' . ($isEdit ? 'updating' : 'adding') . ' a vehicle model'">
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-car"></i> What is a model?
        </h3>
        <p class="mi-guide-text">
            A vehicle model is a specific line under a make (e.g. Toyota Corolla). Products can be linked to one or more models for fitment lookup.
        </p>
    </section>

    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-lightbulb"></i> Best practices
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Pick the correct make before entering the model name.</li>
            <li><i class="fas fa-check"></i> Use common model names customers recognise.</li>
            <li><i class="fas fa-check"></i> The same model name can exist under different makes.</li>
        </ul>
    </section>
</x-module.form-guide>

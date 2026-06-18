@props(['productName' => null])

<div class="mi-form-grid">
    <div class="mi-span-full">
        <label for="name" class="mi-field-label">
            <i class="fas fa-tag"></i> Product Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $productName->name ?? '')" required autofocus placeholder="e.g. Oil Filter" />
        <p class="mi-field-hint">Generic part type name — must be unique (e.g. Brake Pad, not brand-specific).</p>
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this name can be selected when creating products.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $productName->is_active ?? true))>
    </label>
</div>

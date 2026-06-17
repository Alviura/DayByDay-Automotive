@props(['vehicleMake' => null])

<div class="mi-form-grid">
    <div class="mi-span-full">
        <label for="name" class="mi-field-label">
            <i class="fas fa-industry"></i> Make Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $vehicleMake->name ?? '')" required autofocus placeholder="e.g. Toyota" />
        <p class="mi-field-hint">Manufacturer or brand name — must be unique across all makes.</p>
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this make appears when assigning models and product fitment.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $vehicleMake->is_active ?? true))>
    </label>
</div>

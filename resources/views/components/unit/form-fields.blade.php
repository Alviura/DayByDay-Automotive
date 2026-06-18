@props(['unit' => null])

<div class="mi-form-grid">
    <div>
        <label for="name" class="mi-field-label">
            <i class="fas fa-ruler"></i> Unit Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $unit->name ?? '')" required autofocus placeholder="e.g. Piece" />
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <label for="abbreviation" class="mi-field-label">
            <i class="fas fa-font"></i> Abbreviation
        </label>
        <x-text-input id="abbreviation" name="abbreviation" type="text" class="mi-input block w-full uppercase" :value="old('abbreviation', $unit->abbreviation ?? '')" maxlength="20" placeholder="e.g. PCS" />
        <p class="mi-field-hint">Short code shown on labels and invoices — optional.</p>
        <x-input-error :messages="$errors->get('abbreviation')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this unit can be selected when creating products.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $unit->is_active ?? true))>
    </label>
</div>

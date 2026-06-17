@props(['shop' => null])

<div class="mi-form-grid">
    <div>
        <label for="name" class="mi-field-label">
            <i class="fas fa-tag"></i> Shop Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $shop->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <label for="code" class="mi-field-label">
            <i class="fas fa-barcode"></i> Shop Code
        </label>
        <x-text-input id="code" name="code" type="text" class="mi-input block w-full uppercase" :value="old('code', $shop->code ?? '')" required maxlength="20" placeholder="e.g. SH-DTOWN" />
        <p class="mi-field-hint">Short unique identifier — letters, numbers, and dashes only.</p>
        <x-input-error :messages="$errors->get('code')" class="mt-1.5" />
    </div>

    <div class="mi-span-full">
        <label for="address" class="mi-field-label">
            <i class="fas fa-map-pin"></i> Address
        </label>
        <x-text-input id="address" name="address" type="text" class="mi-input block w-full" :value="old('address', $shop->address ?? '')" placeholder="Street, city, region…" />
        <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
    </div>

    <div>
        <label for="phone" class="mi-field-label">
            <i class="fas fa-phone"></i> Phone
        </label>
        <x-text-input id="phone" name="phone" type="text" class="mi-input block w-full" :value="old('phone', $shop->phone ?? '')" placeholder="+254 …" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this shop can be assigned to staff and process sales and stock.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $shop->is_active ?? true))>
    </label>
</div>

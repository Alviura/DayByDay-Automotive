@props(['supplier' => null])

<div class="mi-form-grid">
    <div>
        <label for="name" class="mi-field-label">
            <i class="fas fa-building"></i> Supplier Name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $supplier->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <label for="code" class="mi-field-label">
            <i class="fas fa-barcode"></i> Supplier Code
        </label>
        <x-text-input id="code" name="code" type="text" class="mi-input block w-full uppercase" :value="old('code', $supplier->code ?? '')" required maxlength="20" placeholder="e.g. SUP-AGL" />
        <p class="mi-field-hint">Short unique identifier — letters, numbers, and dashes only.</p>
        <x-input-error :messages="$errors->get('code')" class="mt-1.5" />
    </div>

    <div>
        <label for="contact_person" class="mi-field-label">
            <i class="fas fa-user"></i> Contact Person
        </label>
        <x-text-input id="contact_person" name="contact_person" type="text" class="mi-input block w-full" :value="old('contact_person', $supplier->contact_person ?? '')" />
        <x-input-error :messages="$errors->get('contact_person')" class="mt-1.5" />
    </div>

    <div>
        <label for="email" class="mi-field-label">
            <i class="fas fa-envelope"></i> Email
        </label>
        <x-text-input id="email" name="email" type="email" class="mi-input block w-full" :value="old('email', $supplier->email ?? '')" placeholder="orders@supplier.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
    </div>

    <div>
        <label for="phone" class="mi-field-label">
            <i class="fas fa-phone"></i> Phone
        </label>
        <x-text-input id="phone" name="phone" type="text" class="mi-input block w-full" :value="old('phone', $supplier->phone ?? '')" placeholder="+254 …" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
    </div>

    <div>
        <label for="country" class="mi-field-label">
            <i class="fas fa-globe"></i> Country
        </label>
        <x-text-input id="country" name="country" type="text" class="mi-input block w-full" :value="old('country', $supplier->country ?? '')" placeholder="Kenya" />
        <x-input-error :messages="$errors->get('country')" class="mt-1.5" />
    </div>

    <div>
        <label for="currency" class="mi-field-label">
            <i class="fas fa-coins"></i> Currency
        </label>
        <select id="currency" name="currency" class="mi-select" required>
            @foreach (['KES', 'USD', 'EUR', 'GBP', 'JPY', 'CNY'] as $currency)
                <option value="{{ $currency }}" @selected(old('currency', $supplier->currency ?? 'KES') === $currency)>{{ $currency }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('currency')" class="mt-1.5" />
    </div>

    <div>
        <label for="lead_time_days" class="mi-field-label">
            <i class="fas fa-truck"></i> Lead Time (days)
        </label>
        <x-text-input id="lead_time_days" name="lead_time_days" type="number" min="0" max="365" class="mi-input block w-full" :value="old('lead_time_days', $supplier->lead_time_days ?? '')" placeholder="e.g. 14" />
        <x-input-error :messages="$errors->get('lead_time_days')" class="mt-1.5" />
    </div>

    <div>
        <label for="rating" class="mi-field-label">
            <i class="fas fa-star"></i> Rating (0–5)
        </label>
        <x-text-input id="rating" name="rating" type="number" min="0" max="5" step="0.1" class="mi-input block w-full" :value="old('rating', $supplier->rating ?? '')" placeholder="e.g. 4.5" />
        <x-input-error :messages="$errors->get('rating')" class="mt-1.5" />
    </div>

    <div class="mi-span-full">
        <label for="address" class="mi-field-label">
            <i class="fas fa-map-pin"></i> Address
        </label>
        <x-text-input id="address" name="address" type="text" class="mi-input block w-full" :value="old('address', $supplier->address ?? '')" placeholder="Street, city, region…" />
        <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active status</p>
        <p class="mi-toggle-desc">When active, this supplier can be linked to products and procurement orders.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $supplier->is_active ?? true))>
    </label>
</div>

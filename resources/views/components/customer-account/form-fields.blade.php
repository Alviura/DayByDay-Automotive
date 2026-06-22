@props(['customerAccount' => null])

<div class="mi-form-grid">
    <div class="mi-span-full">
        <label for="name" class="mi-field-label">
            <i class="fas fa-bus"></i> Account name
        </label>
        <x-text-input id="name" name="name" type="text" class="mi-input block w-full" :value="old('name', $customerAccount->name ?? '')" required autofocus placeholder="e.g. Jane's PSV Fleet" />
        <p class="mi-field-hint">Display name shown in Order Entry and on monthly invoices.</p>
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <label for="contact_name" class="mi-field-label">
            <i class="fas fa-user"></i> Contact person
        </label>
        <x-text-input id="contact_name" name="contact_name" type="text" class="mi-input block w-full" :value="old('contact_name', $customerAccount->contact_name ?? '')" placeholder="Fleet manager" />
        <x-input-error :messages="$errors->get('contact_name')" class="mt-1.5" />
    </div>

    <div>
        <label for="phone" class="mi-field-label">
            <i class="fas fa-phone"></i> Phone
        </label>
        <x-text-input id="phone" name="phone" type="text" class="mi-input block w-full" :value="old('phone', $customerAccount->phone ?? '')" placeholder="+254 …" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
    </div>

    <div class="mi-span-full">
        <label for="email" class="mi-field-label">
            <i class="fas fa-envelope"></i> Email
        </label>
        <x-text-input id="email" name="email" type="email" class="mi-input block w-full" :value="old('email', $customerAccount->email ?? '')" placeholder="billing@example.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
    </div>

    <div>
        <label for="billing_terms" class="mi-field-label">
            <i class="fas fa-calendar-days"></i> Billing terms
        </label>
        <select id="billing_terms" name="billing_terms" class="mi-select block w-full">
            @foreach (['monthly' => 'Monthly (end of month)', 'weekly' => 'Weekly', 'on_delivery' => 'On delivery'] as $value => $label)
                <option value="{{ $value }}" @selected(old('billing_terms', $customerAccount->billing_terms ?? 'monthly') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('billing_terms')" class="mt-1.5" />
    </div>

    <div>
        <label for="credit_limit" class="mi-field-label">
            <i class="fas fa-gauge-high"></i> Credit limit (KES)
        </label>
        <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mi-input block w-full" :value="old('credit_limit', $customerAccount->credit_limit ?? '')" placeholder="Optional — leave blank for no cap" />
        <p class="mi-field-hint">Order Entry blocks new credit sales when outstanding exceeds this amount.</p>
        <x-input-error :messages="$errors->get('credit_limit')" class="mt-1.5" />
    </div>

    <div class="mi-span-full">
        <label for="notes" class="mi-field-label">
            <i class="fas fa-note-sticky"></i> Notes
        </label>
        <textarea id="notes" name="notes" rows="3" class="mi-input block w-full" placeholder="Internal notes — vehicle types, payment preferences…">{{ old('notes', $customerAccount->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active account</p>
        <p class="mi-toggle-desc">When active, this account appears in Order Entry fleet mode. Deactivate instead of deleting when pausing credit.</p>
    </div>
    <label for="is_active" class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               class="mi-toggle-check"
               @checked(old('is_active', $customerAccount->is_active ?? true))>
    </label>
</div>

@php
    $currentRole = old('role', isset($user) ? $user->roles->pluck('name')->first() : null);
@endphp

<div class="mi-form-grid">
    <div>
        <label class="mi-field-label" for="name"><i class="fas fa-user"></i> Full name</label>
        <input id="name" name="name" type="text" class="mi-input" value="{{ old('name', $user->name ?? '') }}" required autofocus>
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="email"><i class="fas fa-envelope"></i> Email</label>
        <input id="email" name="email" type="email" class="mi-input" value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="phone"><i class="fas fa-phone"></i> Phone</label>
        <input id="phone" name="phone" type="text" class="mi-input" value="{{ old('phone', $user->phone ?? '') }}" placeholder="Optional">
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="role"><i class="fas fa-user-tag"></i> Role</label>
        <select id="role" name="role" class="mi-select" required>
            <option value="">— Select role —</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected($currentRole === $role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="shop_id"><i class="fas fa-store"></i> Shop</label>
        <select id="shop_id" name="shop_id" class="mi-select">
            <option value="">— None —</option>
            @foreach ($shops as $shop)
                <option value="{{ $shop->id }}" @selected((string) old('shop_id', $user->shop_id ?? '') === (string) $shop->id)>{{ $shop->name }}</option>
            @endforeach
        </select>
        <p class="mi-field-hint">For shop managers and attendants.</p>
        @error('shop_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="warehouse_id"><i class="fas fa-warehouse"></i> Warehouse</label>
        <select id="warehouse_id" name="warehouse_id" class="mi-select">
            <option value="">— None —</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $user->warehouse_id ?? '') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
        <p class="mi-field-hint">For warehouse managers.</p>
        @error('warehouse_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="password"><i class="fas fa-lock"></i> {{ isset($user) ? 'New password' : 'Password' }}</label>
        <input id="password" name="password" type="password" class="mi-input" autocomplete="new-password" @unless(isset($user)) required @endunless>
        @if (isset($user))
            <p class="mi-field-hint">Leave blank to keep the current password.</p>
        @endif
        @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="mi-field-label" for="password_confirmation"><i class="fas fa-lock"></i> Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="mi-input" autocomplete="new-password">
    </div>
</div>

<div class="mi-toggle-row">
    <div class="mi-toggle-copy">
        <p class="mi-toggle-title">Active account</p>
        <p class="mi-toggle-desc">Inactive users cannot sign in to the system.</p>
    </div>
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="is_active" name="is_active" value="1" class="mi-toggle-check"
        @checked(old('is_active', $user->is_active ?? true))>
</div>

@php
    $currentRole = old('role', isset($user) ? $user->roles->pluck('name')->first() : null);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" :value="__('Role')" />
        <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">— Select role —</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected($currentRole === $role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="shop_id" :value="__('Shop (optional)')" />
        <select id="shop_id" name="shop_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— None —</option>
            @foreach ($shops as $shop)
                <option value="{{ $shop->id }}" @selected((string) old('shop_id', $user->shop_id ?? '') === (string) $shop->id)>{{ $shop->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('shop_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="warehouse_id" :value="__('Warehouse (optional)')" />
        <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— None —</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $user->warehouse_id ?? '') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('warehouse_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="isset($user) ? __('New Password (leave blank to keep)') : __('Password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
    </div>
</div>

<div class="mt-6">
    <label for="is_active" class="inline-flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            @checked(old('is_active', $user->is_active ?? true)) >
        <span class="ms-2 text-sm text-gray-600">{{ __('Active (can sign in)') }}</span>
    </label>
</div>

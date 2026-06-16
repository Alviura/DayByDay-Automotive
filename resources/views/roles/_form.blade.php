@php
    $assigned = $assigned ?? old('permissions', []);
@endphp

<div class="max-w-sm">
    <x-input-label for="name" :value="__('Role Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $role->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-6">
    <h3 class="font-semibold text-gray-800 mb-2">Permissions</h3>
    <x-input-error :messages="$errors->get('permissions')" class="mb-2" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($permissions as $group => $items)
            <div class="border border-gray-200 rounded-md p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-700 capitalize">{{ str_replace('-', ' ', $group) }}</h4>
                    <label class="text-xs text-gray-500 inline-flex items-center gap-1">
                        <input type="checkbox" class="rounded border-gray-300" onchange="this.closest('div').parentNode.querySelectorAll('input[name=\'permissions[]\']').forEach(c => c.checked = this.checked)">
                        all
                    </label>
                </div>
                <div class="space-y-1">
                    @foreach ($items as $permission)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @checked(in_array($permission->name, old('permissions', $assigned), true))>
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

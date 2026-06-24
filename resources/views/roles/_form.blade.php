@php
    $assigned = $assigned ?? old('permissions', []);
    $isCore = isset($role) && in_array($role->name, $coreRoles ?? [], true);
@endphp

<div class="mi-form-grid">
    <div class="mi-span-full">
        <label class="mi-field-label" for="name"><i class="fas fa-user-tag"></i> Role name</label>
        <input id="name" name="name" type="text" class="mi-input" value="{{ old('name', $role->name ?? '') }}" required autofocus
            @if ($isCore) readonly @endif>
        @if ($isCore)
            <p class="mi-field-hint">Core system role names cannot be changed.</p>
        @endif
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6">
    <div class="rol-perm-toolbar">
        <div>
            <p class="text-sm font-bold text-gray-900">Permissions</p>
            <p class="text-xs text-gray-500 mt-0.5">Grant access by module — users inherit these through the role.</p>
        </div>
        <div class="mi-input-wrap" style="max-width:16rem">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="rol-perm-filter" class="mi-input" placeholder="Filter permissions…"
                oninput="document.querySelectorAll('.rol-perm-group').forEach(g => {
                    const q = this.value.toLowerCase();
                    const text = g.textContent.toLowerCase();
                    g.style.display = !q || text.includes(q) ? '' : 'none';
                })">
        </div>
    </div>

    @error('permissions')<p class="text-xs text-red-600 mb-3">{{ $message }}</p>@enderror

    <div class="rol-perm-grid">
        @foreach ($permissions as $group)
            <div class="rol-perm-group">
                <div class="rol-perm-group-head">
                    <span class="rol-perm-group-title">{{ $group['label'] }}</span>
                    <label class="rol-perm-group-toggle">
                        <input type="checkbox" class="rol-group-toggle"
                            onchange="this.closest('.rol-perm-group').querySelectorAll('input[name=\'permissions[]\']').forEach(c => c.checked = this.checked)">
                        Select all
                    </label>
                </div>
                <div class="rol-perm-list">
                    @foreach ($group['permissions'] as $permission)
                        <label class="rol-perm-item">
                            <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                                @checked(in_array($permission['name'], $assigned, true))>
                            <span>
                                <strong>{{ $permission['label'] }}</strong>
                                <code class="block text-[.65rem] text-gray-400 mt-0.5">{{ $permission['name'] }}</code>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

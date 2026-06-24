<x-app-layout :title="'Edit — '.$role->name">

    @push('styles')
        <x-module.page-index-styles />
        @include('roles.partials.page-styles')
    @endpush

    @php
        $isCore = in_array($role->name, \App\Http\Controllers\RoleController::CORE_ROLES, true);
        $meta = match ($role->name) {
            'Administrator' => ['icon' => 'fa-user-shield', 'avatar' => 'admin'],
            'Shop Manager' => ['icon' => 'fa-store', 'avatar' => 'shop'],
            'Warehouse Manager' => ['icon' => 'fa-warehouse', 'avatar' => 'warehouse'],
            'Shop Attendant' => ['icon' => 'fa-cash-register', 'avatar' => 'attendant'],
            default => ['icon' => 'fa-user-tag', 'avatar' => 'custom'],
        };
    @endphp

    <div class="mi-page rol-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="rol-avatar {{ $meta['avatar'] }}" style="width:2.75rem;height:2.75rem;font-size:1rem;border-radius:10px">
                    <i class="fas {{ $meta['icon'] }}"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $role->name }}</h1>
                        @if ($isCore)
                            <span class="rol-pill rol-pill-core"><i class="fas fa-lock text-[.58rem]"></i> Core role</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">
                        {{ number_format($role->permissions_count) }} permissions · {{ number_format($role->users_count) }} users assigned
                    </p>
                </div>
            </div>
            <a href="{{ route('roles.index') }}" class="mi-btn-ghost">
                <i class="fas fa-arrow-left text-xs"></i> Back to Roles
            </a>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Permissions</p>
                    <p class="mi-kpi-value">{{ number_format($role->permissions_count) }}</p>
                    <p class="rol-kpi-sub">Currently granted</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-key"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Users</p>
                    <p class="mi-kpi-value">{{ number_format($role->users_count) }}</p>
                    <p class="rol-kpi-sub">Assigned to this role</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Modules</p>
                    <p class="mi-kpi-value orange">{{ number_format($permissions->count()) }}</p>
                    <p class="rol-kpi-sub">Permission groups</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-layer-group"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Guard</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ $role->guard_name }}</p>
                    <p class="rol-kpi-sub">Authentication guard</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-shield"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="mi-card mi-form-main">
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-pen-to-square text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Edit permissions</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    <div class="mi-form-body">
                        @include('roles._form', ['coreRoles' => \App\Http\Controllers\RoleController::CORE_ROLES])
                    </div>
                    <div class="mi-form-actions">
                        <a href="{{ route('roles.index') }}" class="mi-btn-ghost">Cancel</a>
                        <button type="submit" class="mi-btn-orange">
                            <i class="fas fa-check text-xs"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
            @include('roles.partials.form-guide')
        </div>
    </div>
</x-app-layout>

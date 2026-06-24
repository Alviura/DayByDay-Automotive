<x-app-layout title="Roles & Permissions">

    @push('styles')
        <x-module.page-index-styles />
        @include('roles.partials.page-styles')
    @endpush

    <div class="mi-page rol-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-shield-halved"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Roles &amp; Permissions</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Define what each role can access across the system.</p>
                </div>
            </div>
            @can('roles.manage')
                <a href="{{ route('roles.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> New Role
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Roles</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total_roles']) }}</p>
                    <p class="rol-kpi-sub">{{ number_format($stats['custom_roles']) }} custom</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user-tag"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Permissions</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total_permissions']) }}</p>
                    <p class="rol-kpi-sub">Across all modules</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-key"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Users</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['assigned_users']) }}</p>
                    <p class="rol-kpi-sub">Team accounts</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Core Roles</p>
                    <p class="mi-kpi-value">{{ count($coreRoles) }}</p>
                    <p class="rol-kpi-sub">Protected system roles</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-lock"></i></div>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    <strong class="text-gray-700">{{ $roles->count() }}</strong> roles configured
                </p>
                <a href="{{ route('users.index') }}" class="text-xs font-semibold text-violet-600 hover:underline">
                    <i class="fas fa-users text-[.65rem]"></i> Manage users
                </a>
            </div>

            <div class="mi-table-wrap">
                @if ($roles->isEmpty())
                    <div class="mi-show-empty py-16">
                        <i class="fas fa-shield-halved"></i>
                        <p class="font-semibold text-gray-600">No roles defined</p>
                        @can('roles.manage')
                            <a href="{{ route('roles.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                <i class="fas fa-plus text-xs"></i> New Role
                            </a>
                        @endcan
                    </div>
                @else
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Permissions</th>
                                <th>Users</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                @php
                                    $meta = match ($role->name) {
                                        'Administrator' => ['icon' => 'fa-user-shield', 'avatar' => 'admin', 'desc' => 'Full system access'],
                                        'Shop Manager' => ['icon' => 'fa-store', 'avatar' => 'shop', 'desc' => 'Shop operations & sales'],
                                        'Warehouse Manager' => ['icon' => 'fa-warehouse', 'avatar' => 'warehouse', 'desc' => 'Warehouse & procurement'],
                                        'Shop Attendant' => ['icon' => 'fa-cash-register', 'avatar' => 'attendant', 'desc' => 'Counter & POS'],
                                        default => ['icon' => 'fa-user-tag', 'avatar' => 'custom', 'desc' => 'Custom role'],
                                    };
                                    $isCore = in_array($role->name, $coreRoles, true);
                                @endphp
                                <tr class="rol-index-row" onclick="window.location='{{ route('roles.edit', $role) }}'">
                                    <td>
                                        <div class="rol-person-cell">
                                            <div class="rol-avatar {{ $meta['avatar'] }}">
                                                <i class="fas {{ $meta['icon'] }}"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="rol-person-name">{{ $role->name }}</p>
                                                <p class="rol-person-sub">{{ $meta['desc'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="rol-count">
                                            <i class="fas fa-key"></i>
                                            {{ number_format($role->permissions_count) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="rol-count">
                                            <i class="fas fa-users"></i>
                                            {{ number_format($role->users_count) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($isCore)
                                            <span class="rol-pill rol-pill-core"><i class="fas fa-lock text-[.58rem]"></i> Core</span>
                                        @else
                                            <span class="rol-pill rol-pill-custom">Custom</span>
                                        @endif
                                    </td>
                                    <td onclick="event.stopPropagation()">
                                        <div class="flex items-center gap-1.5">
                                            @can('roles.manage')
                                                <a href="{{ route('roles.edit', $role) }}" class="mi-action edit" title="Edit"><i class="fas fa-pen"></i></a>
                                                @unless ($isCore)
                                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline" data-confirm="Delete role {{ addslashes($role->name) }}?" data-confirm-variant="danger">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="mi-action del" title="Delete"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @endunless
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

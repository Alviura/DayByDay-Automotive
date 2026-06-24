<x-app-layout title="Users">

    @push('styles')
        <x-module.page-index-styles />
        @include('users.partials.page-styles')
    @endpush

    <div class="mi-page usr-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'role', 'sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-users"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Users</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Manage team accounts, roles, and location assignments.</p>
                </div>
            </div>
            @can('users.create')
                <a href="{{ route('users.create') }}" class="mi-btn-orange">
                    <i class="fas fa-user-plus text-xs"></i> New User
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Users</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="usr-kpi-sub">{{ number_format($stats['active']) }} active</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Active</p>
                    <p class="mi-kpi-value">{{ number_format($stats['active']) }}</p>
                    <p class="usr-kpi-sub">Can sign in</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Inactive</p>
                    <p class="mi-kpi-value">{{ number_format($stats['inactive']) }}</p>
                    <p class="usr-kpi-sub">Access disabled</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-ban"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Recent Logins</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['recent_logins']) }}</p>
                    <p class="usr-kpi-sub">Last 30 days</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-right-to-bracket"></i></div>
            </div>
        </div>

        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Quick filter</p>
            <div class="usr-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $statusFilter === $step['key'];
                        $params = array_merge(request()->except('page', 'status'), ['status' => $step['key'] ?: null]);
                        $params = array_filter($params, fn ($v) => $v !== null && $v !== '');
                    @endphp
                    <a href="{{ route('users.index', $params) }}" class="usr-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="usr-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="usr-pipe-count">{{ number_format($step['count']) }}</span>
                        <span class="usr-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            <form method="GET" x-show="filtersOpen" x-transition>
                @if ($statusFilter)
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <div class="mi-filter-grid">
                    <div>
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or phone…" class="mi-input">
                        </div>
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-user-tag"></i> Role</label>
                        <select name="role" class="mi-select">
                            <option value="">All roles</option>
                            @foreach ($roleOptions as $roleName)
                                <option value="{{ $roleName }}" @selected(request('role') === $roleName)>{{ $roleName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('users.index', $statusFilter ? ['status' => $statusFilter] : []) }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $users->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $users->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $users->total() }}</strong> users
                </p>
            </div>

            <div class="mi-table-wrap">
                @if ($users->isEmpty())
                    <div class="mi-show-empty py-16">
                        <i class="fas fa-users"></i>
                        <p class="font-semibold text-gray-600">No users found</p>
                        <p class="text-sm text-gray-400 mt-1">Try adjusting filters or add a new user.</p>
                        @can('users.create')
                            <a href="{{ route('users.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                <i class="fas fa-user-plus text-xs"></i> New User
                            </a>
                        @endcan
                    </div>
                @else
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Location</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="usr-index-row" onclick="window.location='{{ route('users.show', $user) }}'">
                                    <td>
                                        <div class="usr-person-cell">
                                            <div class="usr-avatar {{ $user->is_active ? '' : 'inactive' }}">{{ $user->initials() }}</div>
                                            <div class="min-w-0">
                                                <p class="usr-person-name truncate">{{ $user->name }}</p>
                                                <p class="usr-person-sub truncate">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($user->roleName())
                                            <span class="usr-role-pill {{ $user->rolePillClass() }}">{{ $user->roleName() }}</span>
                                        @else
                                            <span class="usr-loc-empty">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($user->locationLabel())
                                            <span class="usr-loc-pill">
                                                <i class="fas {{ $user->locationType() === 'warehouse' ? 'fa-warehouse' : 'fa-store' }}"></i>
                                                {{ $user->locationLabel() }}
                                            </span>
                                        @else
                                            <span class="usr-loc-empty">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-gray-500">
                                        {{ $user->last_login_at?->diffForHumans(short: true) ?? 'Never' }}
                                    </td>
                                    <td>
                                        @if ($user->is_active)
                                            <span class="mi-status-active">Active</span>
                                        @else
                                            <span class="mi-status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td onclick="event.stopPropagation()">
                                        <div class="flex items-center gap-1.5">
                                            @can('users.view')
                                                <a href="{{ route('users.show', $user) }}" class="mi-action view" title="View"><i class="fas fa-eye"></i></a>
                                            @endcan
                                            @can('users.edit')
                                                <a href="{{ route('users.edit', $user) }}" class="mi-action edit" title="Edit"><i class="fas fa-pen"></i></a>
                                            @endcan
                                            @can('users.delete')
                                                @if ($user->id !== auth()->id())
                                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" data-confirm="Archive {{ addslashes($user->name) }}?" data-confirm-variant="danger">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="mi-action del" title="Archive"><i class="fas fa-box-archive"></i></button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if ($users->hasPages())
                <div class="mi-card-foot">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

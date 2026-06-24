<x-app-layout :title="$user->name">

    @push('styles')
        <x-module.page-index-styles />
        @include('users.partials.page-styles')
    @endpush

    <div class="mi-page usr-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="usr-show-hero">
                <div class="usr-avatar lg {{ $user->is_active ? '' : 'inactive' }}">{{ $user->initials() }}</div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $user->name }}</h1>
                        @if ($user->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                        @if ($user->roleName())
                            <span class="usr-role-pill {{ $user->rolePillClass() }}">{{ $user->roleName() }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $user->email }}</p>
                    @if ($user->phone)
                        <p class="mt-0.5 text-xs text-gray-400"><i class="fas fa-phone text-[.6rem]"></i> {{ $user->phone }}</p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('users.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
            @can('users.edit')
                    <a href="{{ route('users.edit', $user) }}" class="mi-btn-orange"><i class="fas fa-pen text-xs"></i> Edit</a>
            @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Logins</p>
                    <p class="mi-kpi-value">{{ number_format($loginCount) }}</p>
                    <p class="usr-kpi-sub">Recorded sessions</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-clock-rotate-left"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Last 30 Days</p>
                    <p class="mi-kpi-value">{{ number_format($recentLoginCount) }}</p>
                    <p class="usr-kpi-sub">Recent sign-ins</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Last Login</p>
                    <p class="mi-kpi-value orange" style="font-size:1rem">{{ $user->last_login_at?->diffForHumans(short: true) ?? 'Never' }}</p>
                    <p class="usr-kpi-sub">{{ $user->last_login_at?->format('d M Y H:i') ?? 'No activity yet' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-right-to-bracket"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Location</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ $user->locationLabel() ?? 'Unassigned' }}</p>
                    <p class="usr-kpi-sub">
                        @if ($user->shop)
                            Shop · {{ $user->shop->code }}
                        @elseif ($user->warehouse)
                            Warehouse · {{ $user->warehouse->code }}
                        @else
                            No shop or warehouse
                        @endif
                    </p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-location-dot"></i></div>
            </div>
        </div>

        <div class="usr-show-grid">
            <div class="space-y-5">
                <div class="mi-card">
                    <div class="mi-card-head">
                        <span class="text-sm font-semibold text-gray-900">Profile</span>
                    </div>
                    <div class="mi-detail-grid">
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-envelope"></i> Email</p>
                            <p class="mi-detail-value">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-phone"></i> Phone</p>
                            <p class="mi-detail-value">{{ $user->phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-user-tag"></i> Role</p>
                            <p class="mi-detail-value">
                                @if ($user->roleName())
                                    <span class="usr-role-pill {{ $user->rolePillClass() }}">{{ $user->roleName() }}</span>
                                @else
                                    <span class="mi-detail-empty">Not assigned</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-toggle-on"></i> Status</p>
                            <p class="mi-detail-value">{{ $user->is_active ? 'Active — can sign in' : 'Inactive — access disabled' }}</p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-store"></i> Shop</p>
                            <p class="mi-detail-value">{{ $user->shop?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-warehouse"></i> Warehouse</p>
                            <p class="mi-detail-value">{{ $user->warehouse?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-calendar-plus"></i> Created</p>
                            <p class="mi-detail-value">{{ $user->created_at?->format('d M Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="mi-detail-label"><i class="fas fa-shield-halved"></i> Email verified</p>
                            <p class="mi-detail-value">{{ $user->email_verified_at ? $user->email_verified_at->format('d M Y') : 'Not verified' }}</p>
                        </div>
                    </div>
            </div>

                <div class="usr-doc-card">
                    <div class="usr-doc-head">
                        <div>
                            <h2>Login History</h2>
                            <p>Last 20 sign-in sessions</p>
                        </div>
                    </div>
                    <div class="mi-table-wrap">
                        @if ($user->logins->isEmpty())
                            <div class="mi-show-empty py-12">
                                <i class="fas fa-right-to-bracket"></i>
                                <p>No login history recorded yet.</p>
                            </div>
                        @else
                            <table class="mi-table usr-login-row">
                    <thead>
                                    <tr>
                                        <th>Logged in</th>
                                        <th>Logged out</th>
                                        <th>IP address</th>
                                        <th>Device</th>
                        </tr>
                    </thead>
                                <tbody>
                                    @foreach ($user->logins as $login)
                                        <tr>
                                            <td>{{ $login->logged_in_at?->format('d M Y H:i') ?? '—' }}</td>
                                            <td class="text-gray-500">{{ $login->logged_out_at?->format('d M Y H:i') ?? '—' }}</td>
                                            <td class="font-mono text-xs text-gray-600">{{ $login->ip_address ?? '—' }}</td>
                                            <td><span class="usr-login-agent" title="{{ $login->user_agent }}">{{ $login->user_agent ?? '—' }}</span></td>
                            </tr>
                                    @endforeach
                    </tbody>
                </table>
                        @endif
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="mi-card p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Actions</p>
                    <div class="mi-show-actions">
                        @can('users.edit')
                            <a href="{{ route('users.edit', $user) }}" class="mi-btn-orange">
                                <i class="fas fa-pen text-xs"></i> Edit User
                            </a>
                        @endcan
                        @can('users.delete')
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" data-confirm="Archive {{ addslashes($user->name) }}? They will no longer be able to sign in." data-confirm-variant="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mi-btn-danger">
                                        <i class="fas fa-box-archive text-xs"></i> Archive User
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>

                <div class="mi-guide" style="position:static">
                    <div class="mi-guide-head">
                        <div class="mi-guide-icon"><i class="fas fa-circle-info"></i></div>
                        <div>
                            <p class="mi-guide-title">Account summary</p>
                            <p class="mi-guide-subtitle">Quick reference</p>
                        </div>
                    </div>
                    <div class="mi-guide-body">
                        <ul class="mi-show-meta">
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-fingerprint"></i> User ID</span>
                                <span class="mi-show-meta-value mono">#{{ $user->id }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-user-tag"></i> Role</span>
                                <span class="mi-show-meta-value">{{ $user->roleName() ?? 'None' }}</span>
                            </li>
                            <li>
                                <span class="mi-show-meta-label"><i class="fas fa-location-dot"></i> Assignment</span>
                                <span class="mi-show-meta-value">{{ $user->locationLabel() ?? 'Global / unassigned' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>

<x-app-layout title="My Profile">

    @push('styles')
        <x-module.page-index-styles />
        @include('users.partials.page-styles')
        @include('profile.partials.page-styles')
    @endpush

    <div class="mi-page prf-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="prf-hero">
                <div class="prf-avatar">{{ $user->initials() }}</div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $user->name }}</h1>
                        @if ($user->roleName())
                            <span class="usr-role-pill {{ $user->rolePillClass() }}">{{ $user->roleName() }}</span>
                        @endif
                        @if ($user->is_active)
                            <span class="mi-status-active">Active</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $user->email }}</p>
                    @if ($user->locationLabel())
                        <p class="mt-0.5 text-xs text-gray-400">
                            <i class="fas fa-location-dot text-[.6rem]"></i>
                            {{ $user->locationLabel() }}
                        </p>
                    @endif
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="mi-btn-ghost">
                <i class="fas fa-gauge-high text-xs"></i> Dashboard
            </a>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Account</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ $user->roleName() ?? 'Staff' }}</p>
                    <p class="usr-kpi-sub">Assigned role</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-user-tag"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Last Login</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ $user->last_login_at?->diffForHumans(short: true) ?? '—' }}</p>
                    <p class="usr-kpi-sub">{{ $user->last_login_at?->format('d M Y H:i') ?? 'No recorded login' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-right-to-bracket"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Location</p>
                    <p class="mi-kpi-value text-status" style="font-size:1rem">{{ $user->locationLabel() ?? 'Unassigned' }}</p>
                    <p class="usr-kpi-sub">{{ $user->shop ? 'Shop' : ($user->warehouse ? 'Warehouse' : 'No site linked') }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-location-dot"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Member Since</p>
                    <p class="mi-kpi-value orange" style="font-size:1rem">{{ $user->created_at->format('M Y') }}</p>
                    <p class="usr-kpi-sub">{{ $user->created_at->diffForHumans() }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="space-y-5 mi-form-main">
                <div class="mi-card">
                    @include('profile.partials.update-profile-information-form')
                </div>
                <div class="mi-card">
                    @include('profile.partials.update-password-form')
                </div>
                <div class="mi-card prf-danger-card">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <x-module.form-guide subtitle="Your account settings">
                <div class="prf-account-card">
                    <p class="prf-account-label">Signed in as</p>
                    <p class="text-sm font-bold text-cyan-900">{{ $user->name }}</p>
                    <p class="text-xs text-cyan-700 mt-1">{{ $user->email }}</p>
                </div>

                <ul class="mi-guide-list">
                    <li>Update your <strong>name and email</strong> in Profile Information.</li>
                    <li>Use a long, unique <strong>password</strong> and change it periodically.</li>
                    <li>Your <strong>role and location</strong> are managed by an administrator.</li>
                </ul>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Need help?</p>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Contact your system administrator to change your role, shop, or warehouse assignment.
                    </p>
                </div>
            </x-module.form-guide>
        </div>
    </div>
</x-app-layout>

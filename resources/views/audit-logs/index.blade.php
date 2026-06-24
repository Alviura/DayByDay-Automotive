<x-app-layout title="Audit Log">

    @push('styles')
        <x-module.page-index-styles />
        @include('audit-logs.partials.page-styles')
    @endpush

    <div class="mi-page aud-page space-y-5" x-data="{ filtersOpen: {{ ($hasFilters ?? false) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-shield-halved"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Audit Log</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Immutable trace of who changed what, when, and from where.</p>
                </div>
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Events</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="aud-kpi-sub">All time</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-database"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Today</p>
                    <p class="mi-kpi-value">{{ number_format($stats['today']) }}</p>
                    <p class="aud-kpi-sub">{{ number_format($stats['this_week']) }} this week</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-calendar-day"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Creates</p>
                    <p class="mi-kpi-value">{{ number_format($stats['creates']) }}</p>
                    <p class="aud-kpi-sub">New records</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-plus"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Updates</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['updates']) }}</p>
                    <p class="aud-kpi-sub">{{ number_format($stats['deletes']) }} deletions</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-pen"></i></div>
            </div>
        </div>

        <div class="mi-card p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Event type</p>
            <div class="aud-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $actionFilter === $step['key'];
                        $params = array_merge(
                            request()->except('page', 'action'),
                            ['action' => $step['key'] ?: null]
                        );
                        $params = array_filter($params, fn ($v) => $v !== null && $v !== '');
                    @endphp
                    <a href="{{ route('audit-logs.index', $params) }}" class="aud-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="aud-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="aud-pipe-count">{{ number_format($step['count']) }}</span>
                        <span class="aud-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                    @if ($hasFilters)
                        <span class="mi-cat-badge">Active</span>
                    @endif
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            <form method="GET" x-show="filtersOpen" x-transition>
                @if ($actionFilter)
                    <input type="hidden" name="action" value="{{ $actionFilter }}">
                @endif
                <div class="mi-filter-grid">
                    <div>
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Reference, user, module, or action…" class="mi-input">
                        </div>
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-cube"></i> Module</label>
                        <select name="module" class="mi-select">
                            <option value="">All modules</option>
                            @foreach ($modules as $key => $label)
                                <option value="{{ $key }}" @selected(request('module') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-bolt"></i> Action</label>
                        <select name="action" class="mi-select">
                            <option value="">All actions</option>
                            @foreach (['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-user"></i> User</label>
                        <select name="user_id" class="mi-select">
                            <option value="">All users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-calendar"></i> From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mi-input block w-full">
                    </div>
                    <div>
                        <label class="mi-field-label"><i class="fas fa-calendar"></i> To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mi-input block w-full">
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <a href="{{ route('audit-logs.index') }}" class="mi-btn-ghost">Clear</a>
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-filter text-xs"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    <strong class="text-gray-700">{{ number_format($logs->total()) }}</strong>
                    {{ str('entry')->plural($logs->total()) }}
                    @if ($hasFilters)
                        matching filters
                    @endif
                </p>
            </div>

            <div class="mi-table-wrap">
                @if ($logs->isEmpty())
                    <div class="mi-show-empty py-16">
                        <div class="aud-empty-icon"><i class="fas fa-shield-halved"></i></div>
                        <p class="font-semibold text-gray-600">No audit entries found</p>
                        <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto">
                            @if ($hasFilters)
                                Try adjusting your filters or clearing them to see more activity.
                            @else
                                System activity will appear here as users create, update, and delete records.
                            @endif
                        </p>
                        @if ($hasFilters)
                            <a href="{{ route('audit-logs.index') }}" class="mi-btn-ghost mt-4 inline-flex">Clear filters</a>
                        @endif
                    </div>
                @else
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Reference</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr class="aud-index-row" onclick="window.location='{{ route('audit-logs.show', $log) }}'">
                                    <td>
                                        <p class="aud-time-main">{{ $log->created_at->format('d M Y') }}</p>
                                        <p class="aud-time-sub">{{ $log->created_at->format('H:i') }} · {{ $log->created_at->diffForHumans() }}</p>
                                    </td>
                                    <td>
                                        <div class="aud-actor">
                                            <span class="aud-actor-avatar {{ $log->user ? '' : 'system' }}">{{ $log->actorInitials() }}</span>
                                            <div class="min-w-0">
                                                <p class="aud-actor-name truncate">{{ $log->user?->name ?? 'System' }}</p>
                                                @if ($log->user?->email)
                                                    <p class="aud-actor-sub truncate">{{ $log->user->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="{{ $log->actionBadgeClass() }}">
                                            <i class="fas {{ $log->actionIcon() }}"></i>
                                            {{ $log->actionLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ $log->moduleBadgeClass() }}">
                                            <i class="fas {{ $log->moduleIcon() }}"></i>
                                            {{ $log->moduleLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($log->reference_number)
                                            <span class="aud-ref">{{ $log->reference_number }}</span>
                                        @else
                                            <span class="aud-ref muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('audit-logs.show', $log) }}" class="mi-action view" onclick="event.stopPropagation()">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if ($logs->hasPages())
                <div class="mi-card-foot">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

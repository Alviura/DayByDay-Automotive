<x-app-layout title="Approvals">

    @push('styles')
        <x-module.page-index-styles />
        @include('approvals.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'module', 'sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Approval Inbox</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Review pending requests for stock transfers, adjustments, returns, and legacy quotation series.</p>
                </div>
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Awaiting Me</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['pending_mine']) }}</p>
                    <p class="ap-kpi-sub">In your queue</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-inbox"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">All Pending</p>
                    <p class="mi-kpi-value">{{ number_format($stats['pending_all']) }}</p>
                    <p class="ap-kpi-sub">Across all modules</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Completed</p>
                    <p class="mi-kpi-value">{{ number_format($stats['completed']) }}</p>
                    <p class="ap-kpi-sub">Approved or rejected</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Returned</p>
                    <p class="mi-kpi-value">{{ number_format($stats['returned']) }}</p>
                    <p class="ap-kpi-sub">Sent back for revision</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-rotate-left"></i></div>
            </div>
        </div>

        <div class="mi-tab-bar">
            <a href="{{ route('approvals.index', array_merge(request()->except('page'), ['filter' => 'mine'])) }}"
               class="{{ $filter === 'mine' ? 'active' : '' }}">
                <i class="fas fa-inbox"></i> My Queue
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->except('page'), ['filter' => 'requested'])) }}"
               class="{{ $filter === 'requested' ? 'active' : '' }}">
                <i class="fas fa-paper-plane"></i> My Requests
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->except('page'), ['filter' => 'pending'])) }}"
               class="{{ $filter === 'pending' ? 'active' : '' }}">
                <i class="fas fa-hourglass-half"></i> All Pending
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->except('page'), ['filter' => 'completed'])) }}"
               class="{{ $filter === 'completed' ? 'active' : '' }}">
                <i class="fas fa-circle-check"></i> Completed
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->except('page'), ['filter' => 'returned'])) }}"
               class="{{ $filter === 'returned' ? 'active' : '' }}">
                <i class="fas fa-rotate-left"></i> Returned
            </a>
        </div>

        @if (in_array($filter, ['mine', 'pending'], true))
            <div class="mi-card p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Pending by Module</p>
                <div class="ap-pipeline">
                    @foreach ($pipeline as $step)
                        @php
                            $params = array_merge(request()->except('page'), ['filter' => $filter]);
                            if ($step['key'] === '') {
                                unset($params['module']);
                            } else {
                                $params['module'] = $step['key'];
                            }
                            $isActive = request('module', '') === $step['key'];
                        @endphp
                        <a href="{{ route('approvals.index', $params) }}"
                           class="ap-pipe-step {{ $isActive ? 'active' : '' }}">
                            <div class="ap-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                            <span class="ap-pipe-count">{{ $step['count'] }}</span>
                            <span class="ap-pipe-label">{{ $step['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

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
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Reference #, notes, requester…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-layer-group"></i> Module</label>
                        <select name="module" class="mi-select">
                            <option value="">All modules</option>
                            @foreach ($modules as $key => $module)
                                <option value="{{ $key }}" @selected(request('module') === $key)>{{ $module['label'] }}</option>
                            @endforeach
                            <option value="procurement" @selected(request('module') === 'procurement')>Quotation Series (legacy filter)</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="module" @selected(request('sort') === 'module')>Module</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('approvals.index', ['filter' => $filter]) }}" class="mi-btn-ghost">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Reset All
                    </a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $approvals->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $approvals->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $approvals->total() }}</strong> approvals
                    · click a row to open
                </p>
            </div>

            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Module</th>
                            <th>Document</th>
                            <th>Requester</th>
                            <th>Approver</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($approvals as $approval)
                            @php $showUrl = route('approvals.show', $approval); @endphp
                            <tr class="ap-index-row" onclick="window.location='{{ $showUrl }}'">
                                <td class="text-gray-400 font-medium">{{ $approvals->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="mi-cat-badge">
                                        <i class="fas {{ $approval->moduleIcon() }} text-[0.55rem]"></i>
                                        {{ $approval->moduleLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ $showUrl }}" class="mi-pkg-name hover:text-orange-600" onclick="event.stopPropagation()">
                                        {{ $approval->documentTitle() }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $approval->documentReference() }}</p>
                                    @if ($approval->documentSummary())
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $approval->documentSummary() }}</p>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-600">{{ $approval->requester?->name ?? '—' }}</td>
                                <td class="text-sm text-gray-600">{{ $approval->currentApprover?->name ?? '—' }}</td>
                                <td>
                                    <span class="{{ $approval->status->badgeClass() }}">{{ $approval->status->label() }}</span>
                                </td>
                                <td class="text-sm text-gray-500 whitespace-nowrap">{{ $approval->created_at->format('d M Y') }}</td>
                                <td class="text-gray-300"><i class="fas fa-chevron-right text-xs"></i></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="!py-14 text-center text-gray-400">
                                    <i class="fas fa-clipboard-check mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No approvals in this queue</p>
                                    <p class="text-sm text-gray-400 mt-1">Try a different filter or check back when new requests are submitted.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($approvals->hasPages())
                <div class="mi-card-foot">{{ $approvals->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

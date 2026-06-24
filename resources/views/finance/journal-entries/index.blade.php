<x-app-layout title="Journal Ledger">

    @push('styles')
        <x-module.page-index-styles />
        @include('finance.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search','source','sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-book"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Journal Ledger</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Posted system entries and approved manual journals.</p>
                </div>
            </div>
            @can('finance.journal')
                <a href="{{ route('journal-entries.create') }}" class="mi-btn-orange no-print">
                    <i class="fas fa-plus text-xs"></i> Manual Journal
                </a>
            @endcan
        </div>

        @include('finance.partials.nav-tabs', ['active' => 'ledger'])

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Posted</p>
                    <p class="mi-kpi-value">{{ number_format($stats['posted']) }}</p>
                    <p class="fin-kpi-sub">KES {{ number_format($stats['posted_amount'], 0) }} total debits</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Pending Approval</p>
                    <p class="mi-kpi-value">{{ $stats['pending'] }}</p>
                    <p class="fin-kpi-sub">{{ $stats['draft'] }} drafts</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Manual</p>
                    <p class="mi-kpi-value">{{ $stats['manual'] }}</p>
                    <p class="fin-kpi-sub">{{ $stats['voided'] }} voided</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-pen"></i></div>
            </div>
        </div>

        <div class="mi-card p-4 no-print">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Status pipeline</p>
            <div class="fin-pipeline">
                @foreach ($pipeline as $step)
                    @php
                        $isActive = $step['key'] === '' ? ! request('status') : request('status') === $step['key'];
                        $params = $step['key'] === ''
                            ? request()->except('page', 'status')
                            : array_merge(request()->except('page'), ['status' => $step['key']]);
                    @endphp
                    <a href="{{ route('journal-entries.index', $params) }}" class="fin-pipe-step {{ $isActive ? 'active' : '' }}">
                        <div class="fin-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <span class="fin-pipe-count">{{ $step['count'] }}</span>
                        <span class="fin-pipe-label">{{ $step['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mi-card no-print">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Filters</span>
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>
            <form method="GET" x-show="filtersOpen" x-transition>
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Entry # or description…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Source</label>
                        <select name="source" class="mi-select">
                            <option value="">All sources</option>
                            <option value="manual" @selected(request('source') === 'manual')>Manual</option>
                            <option value="system" @selected(request('source') === 'system')>System</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Sort</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">Apply</button>
                    <a href="{{ route('journal-entries.index') }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-table-card">
            @if ($entries->isEmpty())
                <div class="fin-empty">
                    <div class="fin-empty-icon"><i class="fas fa-book"></i></div>
                    <p class="font-semibold text-gray-700">No journal entries yet</p>
                    <p class="text-sm text-gray-500 mt-1">Manual journals appear here after creation; system entries post from operations (F2).</p>
                    @can('finance.journal')
                        <a href="{{ route('journal-entries.create') }}" class="mi-btn-orange mt-4 inline-flex">Create Manual Journal</a>
                    @endcan
                </div>
            @else
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Entry</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr class="fin-index-row" onclick="window.location='{{ route('journal-entries.show', $entry) }}'">
                                <td>
                                    <span class="fin-entry-chip fin-mono">{{ $entry->entry_number }}</span>
                                </td>
                                <td class="text-sm whitespace-nowrap">{{ $entry->entry_date->format('d M Y') }}</td>
                                <td class="text-sm text-gray-700 max-w-sm truncate">{{ $entry->description }}</td>
                                <td>
                                    <span class="fin-badge {{ $entry->source->value === 'manual' ? 'fin-badge-indigo' : 'fin-badge-blue' }}">
                                        {{ $entry->source->label() }}
                                    </span>
                                </td>
                                <td><span class="{{ $entry->status->badgeClass() }}">{{ $entry->status->label() }}</span></td>
                                <td class="text-right fin-tb-balance fin-amt">{{ number_format((float) $entry->total_debit, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mi-table-footer">{{ $entries->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

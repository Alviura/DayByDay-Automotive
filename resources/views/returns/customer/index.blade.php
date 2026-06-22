<x-app-layout title="Customer Returns">

    @push('styles')
        <x-module.page-index-styles />
        @include('returns.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-rotate-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Customer Returns</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Returns linked to completed sales — restock good items and issue refunds after approval.</p>
                </div>
            </div>
            @can('returns.create')
                <a href="{{ route('customer-returns.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> New Return
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                    <p class="rt-kpi-sub">All customer returns</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Draft · Pending</p>
                    <p class="mi-kpi-value">{{ $stats['draft'] + $stats['pending'] }}</p>
                    <p class="rt-kpi-sub">{{ $stats['draft'] }} draft · {{ $stats['pending'] }} awaiting approval</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-pen"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Completed</p>
                    <p class="mi-kpi-value">{{ number_format($stats['completed']) }}</p>
                    <p class="rt-kpi-sub">{{ $stats['rejected'] }} rejected</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Refunds</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['refunds'], 2) }}</p>
                    <p class="rt-kpi-sub">KES refunded on completed returns</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="space-y-5 min-w-0">

                <div class="mi-card p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Pipeline</p>
                    <div class="rt-pipeline">
                        @foreach ($pipeline as $step)
                            @php
                                $params = request()->except('page');
                                if ($step['key'] === '') {
                                    unset($params['status']);
                                } else {
                                    $params['status'] = $step['key'];
                                }
                                $isActive = request('status', '') === $step['key'];
                            @endphp
                            <a href="{{ route('customer-returns.index', $params) }}"
                               class="rt-pipe-step {{ $isActive ? 'active' : '' }}">
                                <div class="rt-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                                <span class="rt-pipe-count">{{ $step['count'] }}</span>
                                <span class="rt-pipe-label">{{ $step['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <p class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-filter text-gray-400 text-xs"></i> Filters
                        </p>
                        <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-ghost text-xs">
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': filtersOpen }"></i>
                        </button>
                    </div>
                    <form method="GET" x-show="filtersOpen" x-transition class="border-t border-gray-100">
                        @if (request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <div class="mi-filter-grid p-4 pb-0">
                            <div class="mi-filter-field">
                                <label class="mi-field-label">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Return #, receipt #, reason…">
                            </div>
                            <div class="mi-filter-field">
                                <label class="mi-field-label">Sort</label>
                                <select name="sort" class="mi-select">
                                    <option value="newest" @selected(request('sort', 'newest') !== 'oldest')>Newest first</option>
                                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                                </select>
                            </div>
                        </div>
                        <div class="mi-filter-actions p-4">
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                            <a href="{{ route('customer-returns.index', request('status') ? ['status' => request('status')] : []) }}" class="mi-btn-ghost">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <p class="text-sm text-gray-500">
                            {{ $returns->total() }} {{ str('return')->plural($returns->total()) }} · click a row to open
                        </p>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Return</th>
                                    <th>Sale</th>
                                    <th>Shop</th>
                                    <th>Lines</th>
                                    <th>Refund</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($returns as $return)
                                    @php $showUrl = route('customer-returns.show', $return); @endphp
                                    <tr class="rt-index-row" onclick="window.location='{{ $showUrl }}'">
                                        <td>
                                            <a href="{{ $showUrl }}" class="rt-ref" onclick="event.stopPropagation()">{{ $return->return_number }}</a>
                                            @if ($return->reason)
                                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[12rem]">{{ Str::limit($return->reason, 40) }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($return->sale)
                                                <span class="font-mono text-sm">{{ $return->sale->receipt_number }}</span>
                                                @if ($return->sale->customerAccount)
                                                    <p class="text-xs text-gray-400 mt-0.5">{{ $return->sale->customerAccount->name }}</p>
                                                @endif
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="text-sm text-gray-600">{{ $return->shop?->name ?? '—' }}</td>
                                        <td class="font-medium">{{ $return->items_count }}</td>
                                        <td class="font-semibold text-gray-700">
                                            @if ($return->status === 'completed')
                                                {{ number_format($return->refund_amount, 2) }}
                                            @elseif ($return->status === 'draft')
                                                <span class="text-gray-400 text-sm">~{{ number_format($return->estimatedRefund(), 2) }}</span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td>@include('returns.partials.status-badge', ['return' => $return])</td>
                                        <td class="text-sm text-gray-500 whitespace-nowrap">{{ $return->created_at->format('d M Y') }}</td>
                                        <td class="text-gray-300"><i class="fas fa-chevron-right text-xs"></i></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="!py-14 text-center">
                                            <div class="rt-empty-icon"><i class="fas fa-rotate-left"></i></div>
                                            <p class="text-gray-500 font-medium">No customer returns match your filters.</p>
                                            @can('returns.create')
                                                <a href="{{ route('customer-returns.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                                    <i class="fas fa-plus text-xs"></i> New Return
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($returns->hasPages())
                        <div class="mi-card-foot">{{ $returns->links() }}</div>
                    @endif
                </div>
            </div>

            @include('returns.partials.index-guide')
        </div>
    </div>
</x-app-layout>

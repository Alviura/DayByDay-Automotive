<x-app-layout title="Transfer Requests">

    @push('styles')
        <x-module.page-index-styles />
        @include('transfers.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'status', 'type', 'sort']) ? 'true' : 'false' }} }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-inbox"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Transfer Requests</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Request stock from warehouse or other shops for your location.</p>
                </div>
            </div>
            @can('transfer_requests.create')
                <a href="{{ route('transfer-requests.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> New Request
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-list"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Awaiting Review</p>
                    <p class="mi-kpi-value">{{ number_format($stats['submitted']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Accepted</p>
                    <p class="mi-kpi-value">{{ number_format($stats['accepted']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Fulfilled</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['fulfilled']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-flag-checkered"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="space-y-5 min-w-0">
                <div class="mi-card p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Pipeline</p>
                    <div class="tr-pipeline">
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
                            <a href="{{ route('transfer-requests.index', $params) }}"
                               class="tr-pipe-step {{ $isActive ? 'active' : '' }}">
                                <div class="tr-pipe-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                                <span class="tr-pipe-count">{{ $step['count'] }}</span>
                                <span class="tr-pipe-label">{{ $step['label'] }}</span>
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
                                <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Request #, notes…">
                            </div>
                            <div class="mi-filter-field">
                                <label class="mi-field-label">Type</label>
                                <select name="type" class="mi-select">
                                    <option value="">All types</option>
                                    <option value="warehouse_to_shop" @selected(request('type') === 'warehouse_to_shop')>From Warehouse</option>
                                    <option value="inter_shop" @selected(request('type') === 'inter_shop')>From Shop</option>
                                </select>
                            </div>
                        </div>
                        <div class="mi-filter-actions p-4">
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                            <a href="{{ route('transfer-requests.index', request('status') ? ['status' => request('status')] : []) }}" class="mi-btn-ghost">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <p class="text-sm text-gray-500">{{ $requests->total() }} {{ str('request')->plural($requests->total()) }}</p>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Route</th>
                                    <th>Type</th>
                                    <th>Lines</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $item)
                                    @php $showUrl = route('transfer-requests.show', $item); @endphp
                                    <tr class="tr-index-row" onclick="window.location='{{ $showUrl }}'">
                                        <td>
                                            <a href="{{ $showUrl }}" class="tr-ref" onclick="event.stopPropagation()">{{ $item->request_number }}</a>
                                            @if ($item->requester)
                                                <p class="text-xs text-gray-400 mt-0.5">{{ $item->requester->name }}</p>
                                            @endif
                                        </td>
                                        <td>@include('transfers.partials.route-display', ['transferRequest' => $item])</td>
                                        <td><span class="tr-type-pill">{{ $item->typeLabel() }}</span></td>
                                        <td class="font-medium">{{ $item->items_count }}</td>
                                        <td>@include('transfers.partials.status-badge', ['request' => $item])</td>
                                        <td class="text-sm text-gray-500 whitespace-nowrap">{{ $item->created_at->format('d M Y') }}</td>
                                        <td class="text-gray-300"><i class="fas fa-chevron-right text-xs"></i></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="!py-14 text-center">
                                            <p class="text-gray-500 font-medium">No transfer requests match your filters.</p>
                                            @can('transfer_requests.create')
                                                <a href="{{ route('transfer-requests.create') }}" class="mi-btn-orange mt-4 inline-flex">
                                                    <i class="fas fa-plus text-xs"></i> New Request
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($requests->hasPages())
                        <div class="mi-card-foot">{{ $requests->links() }}</div>
                    @endif
                </div>
            </div>

            <x-transfer-request.form-guide />
        </div>
    </div>
</x-app-layout>

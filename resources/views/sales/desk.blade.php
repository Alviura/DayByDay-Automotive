<x-app-layout title="Cash Desk">

    @push('styles')
        <x-module.page-index-styles />
        @include('sales.partials.page-styles')
    @endpush

    <div class="mi-page pos-page space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-cash-register"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Cash Desk</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Checkout queue, price negotiation, and payment.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                @can('sales.hold')
                    <a href="{{ route('sales.order', ['shop_id' => $shop->id]) }}" class="mi-btn-ghost">
                        <i class="fas fa-clipboard-list text-xs"></i> Order Entry
                    </a>
                @endcan
                <a href="{{ route('sales.index', ['shop_id' => $shop->id]) }}" class="mi-btn-ghost">
                    <i class="fas fa-receipt text-xs"></i> Sales History
                </a>
            </div>
        </div>

        {{-- Shop bar --}}
        <div class="pos-shop-bar">
            <i class="fas fa-store"></i>
            <span class="text-sm text-gray-600">Checkout at</span>
            @if ($shops->count() > 1 && ! auth()->user()->shop_id)
                <form method="GET" action="{{ route('sales.desk') }}" class="inline-flex">
                    <select name="shop_id" class="mi-select text-sm !py-1 !px-2 font-semibold" onchange="this.form.submit()">
                        @foreach ($shops as $s)
                            <option value="{{ $s->id }}" @selected($s->id === $shop->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <strong>{{ $shop->name }}</strong>
            @endif
            @if ($stats['waiting'] > 0)
                <span class="pos-desk-badge ml-auto">{{ $stats['waiting'] }} waiting</span>
            @endif
        </div>

        {{-- KPIs --}}
        <div class="pos-desk-kpi">
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Waiting</p>
                    <p class="mi-kpi-value">{{ $stats['waiting'] }}</p>
                    <p class="pos-kpi-sub">
                        @if ($stats['oldest_wait_mins'] > 0)
                            Longest wait {{ $stats['oldest_wait_mins'] }} min
                        @else
                            No customers in queue
                        @endif
                    </p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Completed Today</p>
                    <p class="mi-kpi-value">{{ $stats['completed_today'] }}</p>
                    <p class="pos-kpi-sub">Sales finalised</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Today's Takings</p>
                    <p class="mi-kpi-value orange" style="font-size:1.2rem">{{ number_format($stats['today_total'], 0) }}</p>
                    <p class="pos-kpi-sub">KES completed sales</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Avg Ticket</p>
                    <p class="mi-kpi-value" style="font-size:1.2rem">{{ number_format($stats['avg_ticket_today'], 0) }}</p>
                    <p class="pos-kpi-sub">KES per sale today</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-chart-simple"></i></div>
            </div>
        </div>

        <div class="pos-desk-layout">
            {{-- Queue --}}
            <div class="pos-desk-main">
            <div class="pos-panel">
                <div class="pos-panel-head">
                    <div>
                        <span class="pos-panel-title"><i class="fas fa-list-ol"></i> Checkout queue</span>
                        <p class="text-xs text-gray-400 mt-0.5">Click an order to negotiate price and take payment</p>
                    </div>
                    @if ($stats['waiting'] > 0)
                        <span class="pos-cart-count">{{ $stats['waiting'] }}</span>
                    @endif
                </div>

                @if ($queue->count())
                    <div class="pos-queue-grid">
                        @foreach ($queue as $sale)
                            @php
                                $waitMins = $sale->submitted_at?->diffInMinutes(now()) ?? $sale->created_at->diffInMinutes(now());
                                $waitClass = $waitMins >= 15 ? 'urgent' : ($waitMins >= 5 ? 'warn' : 'ok');
                                $badgeClass = $waitMins >= 15 ? 'pos-wait-urgent' : ($waitMins >= 5 ? 'pos-wait-warn' : 'pos-wait-ok');
                            @endphp
                            <a href="{{ route('sales.desk.checkout', $sale) }}" class="pos-queue-item {{ $waitClass }}">
                                <div class="pos-queue-top">
                                    <span class="pos-queue-receipt">{{ $sale->receipt_number }}</span>
                                    <span class="pos-wait-badge {{ $badgeClass }}">{{ $waitMins }} min</span>
                                </div>
                                @if ($sale->isCredit())
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full mt-1">
                                        <i class="fas fa-bus text-[0.6rem]"></i> {{ $sale->customerAccount?->name ?? 'Fleet' }}
                                    </span>
                                @endif
                                <p class="pos-queue-customer">{{ $sale->customer_name ?: ($sale->isCredit() ? 'Fleet account' : 'Walk-in customer') }}</p>
                                @if ($sale->vehicle_plate)
                                    <p class="pos-queue-phone"><i class="fas fa-car text-[0.6rem]"></i> {{ $sale->vehicle_plate }}</p>
                                @endif
                                @if ($sale->customer_phone)
                                    <p class="pos-queue-phone"><i class="fas fa-phone text-[0.6rem]"></i> {{ $sale->customer_phone }}</p>
                                @endif
                                @if ($sale->notes)
                                    <p class="text-xs text-amber-700 bg-amber-50 rounded px-2 py-1 mt-2 line-clamp-2">
                                        <i class="fas fa-sticky-note mr-1"></i>{{ $sale->notes }}
                                    </p>
                                @endif
                                <div class="pos-queue-meta">
                                    <span><i class="fas fa-list"></i> {{ $sale->items_count }} line{{ $sale->items_count === 1 ? '' : 's' }}</span>
                                    <span><i class="fas fa-user"></i> {{ $sale->orderedBy?->name ?? '—' }}</span>
                                    @if ($sale->submitted_at)
                                        <span><i class="fas fa-clock"></i> {{ $sale->submitted_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                <p class="pos-queue-total">KES {{ number_format($sale->total, 2) }}</p>
                                <div class="pos-queue-action">
                                    <span>Checkout & take payment</span>
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if ($queue->hasPages())
                        <div class="mi-card-foot">{{ $queue->links() }}</div>
                    @endif
                @else
                    <div class="pos-desk-empty">
                        <div class="pos-desk-empty-icon"><i class="fas fa-check"></i></div>
                        <p class="font-semibold text-gray-700 text-lg">Queue is clear</p>
                        <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto">
                            No customers waiting. New orders appear here when attendants send them from Order Entry.
                        </p>
                        @can('sales.hold')
                            <a href="{{ route('sales.order', ['shop_id' => $shop->id]) }}" class="mi-btn-orange mt-5 inline-flex">
                                <i class="fas fa-clipboard-list text-xs"></i> Go to Order Entry
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
            </div>

            {{-- Guide --}}
            <div class="pos-desk-side">
                @include('sales.partials.desk-guide')
            </div>
        </div>
    </div>
</x-app-layout>

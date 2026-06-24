<x-app-layout :title="$supplier->name">

    @push('styles')
        <x-module.page-index-styles />
        @include('purchase-orders.partials.page-styles')
        @include('quotation-series.partials.show-styles')
        @include('returns.partials.page-styles')
        @include('suppliers.partials.page-styles')
    @endpush

    @php
        $activeTab = in_array(request('tab'), ['overview', 'scorecard', 'series', 'orders', 'fulfilment'], true)
            ? request('tab')
            : 'overview';
    @endphp

    <div class="mi-page space-y-5" x-data="{ tab: '{{ $activeTab }}' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $supplier->name }}</h1>
                        @if ($supplier->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                        <span class="sp-type-pill {{ $supplier->purchase_type === 'import' ? 'sp-type-import' : 'sp-type-local' }}">
                            <i class="fas fa-{{ $supplier->purchase_type === 'import' ? 'ship' : 'store' }} text-[0.55rem]"></i>
                            {{ $supplier->purchaseTypeLabel() }}
                        </span>
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge"><i class="fas fa-barcode text-[0.55rem]"></i> {{ $supplier->code }}</span>
                        @if ($supplier->country)
                            <span><i class="fas fa-globe text-[0.6rem] text-gray-400"></i> {{ $supplier->country }}</span>
                        @endif
                        <span>{{ $supplier->currency }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('suppliers.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @can('suppliers.manage')
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="mi-btn-orange"><i class="fas fa-pen text-xs"></i> Edit</a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-indigo" style="--kpi-accent:#6366f1">
                <div>
                    <p class="mi-kpi-label">Lifetime Spend</p>
                    <p class="mi-kpi-value" style="font-size:1.05rem">{{ number_format($stats['lifetime_spend'], 0) }}</p>
                    <p class="sp-kpi-sub">{{ $supplier->currency }} · {{ number_format($stats['purchase_orders_total']) }} POs</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Quotation Series</p>
                    <p class="mi-kpi-value">{{ number_format($stats['quotation_series_total']) }}</p>
                    <p class="sp-kpi-sub">{{ number_format($stats['quotation_series_open']) }} open</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-folder-open"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Open POs</p>
                    <p class="mi-kpi-value">{{ number_format($stats['purchase_orders_open']) }}</p>
                    <p class="sp-kpi-sub">{{ number_format($stats['open_po_value'], 0) }} pending</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Rating</p>
                    <p class="mi-kpi-value">{{ $supplier->rating ? number_format($supplier->rating, 1) : '—' }}</p>
                    <p class="sp-kpi-sub">{{ $supplier->lead_time_days ? $supplier->lead_time_days.'d lead time' : 'No lead time set' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-star"></i></div>
            </div>
        </div>

        <div class="mi-tab-bar">
            <button type="button" @click="tab = 'overview'" :class="{ 'active': tab === 'overview' }">
                <i class="fas fa-circle-info"></i> Overview
            </button>
            <button type="button" @click="tab = 'scorecard'" :class="{ 'active': tab === 'scorecard' }">
                <i class="fas fa-chart-line"></i> Scorecard
            </button>
            <button type="button" @click="tab = 'series'" :class="{ 'active': tab === 'series' }">
                <i class="fas fa-folder-open"></i> Series
                @if ($stats['quotation_series_open'] > 0)
                    <span class="mi-cat-badge !text-[0.62rem] !py-0">{{ $stats['quotation_series_open'] }}</span>
                @endif
            </button>
            <button type="button" @click="tab = 'orders'" :class="{ 'active': tab === 'orders' }">
                <i class="fas fa-file-invoice"></i> Orders
                @if ($stats['purchase_orders_open'] > 0)
                    <span class="mi-cat-badge !text-[0.62rem] !py-0">{{ $stats['purchase_orders_open'] }}</span>
                @endif
            </button>
            <button type="button" @click="tab = 'fulfilment'" :class="{ 'active': tab === 'fulfilment' }">
                <i class="fas fa-truck-ramp-box"></i> Fulfilment
            </button>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">

                <div x-show="tab === 'overview'" x-transition>
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-circle-info text-gray-400 text-sm"></i>
                                <span class="text-sm font-semibold">Supplier profile</span>
                            </div>
                        </div>
                        <dl class="mi-detail-grid">
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Code</dt>
                                <dd class="mi-detail-value"><span class="mi-cat-badge">{{ $supplier->code }}</span></dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-building"></i> Name</dt>
                                <dd class="mi-detail-value">{{ $supplier->name }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-user"></i> Contact</dt>
                                <dd class="mi-detail-value">{{ $supplier->contact_person ?? '—' }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-envelope"></i> Email</dt>
                                <dd class="mi-detail-value">
                                    @if ($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}" class="text-orange-600 hover:underline">{{ $supplier->email }}</a>
                                    @else — @endif
                                </dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-phone"></i> Phone</dt>
                                <dd class="mi-detail-value">{{ $supplier->phone ?? '—' }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-globe"></i> Country</dt>
                                <dd class="mi-detail-value">{{ $supplier->country ?? '—' }}</dd>
                            </div>
                            <div class="mi-detail-item mi-span-full">
                                <dt class="mi-detail-label"><i class="fas fa-map-pin"></i> Address</dt>
                                <dd class="mi-detail-value">
                                    @if ($supplier->address)
                                        <span class="mi-dest"><i class="fas fa-map-pin"></i>{{ $supplier->address }}</span>
                                    @else
                                        <span class="mi-detail-empty">Not provided</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-coins"></i> Currency</dt>
                                <dd class="mi-detail-value">{{ $supplier->currency }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-truck"></i> Lead time</dt>
                                <dd class="mi-detail-value">{{ $supplier->lead_time_days ? $supplier->lead_time_days.' days' : '—' }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-star"></i> Rating</dt>
                                <dd class="mi-detail-value">{{ $supplier->rating ? number_format($supplier->rating, 2).' / 5' : '—' }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-toggle-on"></i> Status</dt>
                                <dd class="mi-detail-value">
                                    @if ($supplier->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div x-show="tab === 'scorecard'" x-transition x-cloak>
                    @include('suppliers.partials.scorecard-tab', compact(
                        'supplier', 'stats', 'monthlySpend', 'topProducts', 'openPurchaseOrders'
                    ))
                </div>

                <div x-show="tab === 'series'" x-transition x-cloak>
                    @include('suppliers.partials.series-tab', compact('supplier', 'quotationSeries', 'seriesPipeline'))
                </div>

                <div x-show="tab === 'orders'" x-transition x-cloak>
                    @include('suppliers.partials.orders-tab', compact('supplier', 'purchaseOrders', 'poPipeline', 'stats'))
                </div>

                <div x-show="tab === 'fulfilment'" x-transition x-cloak>
                    @include('suppliers.partials.fulfilment-tab', compact('supplier', 'goodsReceipts', 'supplierReturns', 'stats'))
                </div>
            </div>

            <x-module.show-sidebar
                :model="$supplier"
                :edit-url="route('suppliers.edit', $supplier)"
                :index-url="route('suppliers.index')"
                edit-label="Edit Supplier"
                index-label="All Suppliers"
                manage-permission="suppliers.manage"
            >
                <x-slot:footer>
                    <x-supplier.show-sidebar-extra :supplier="$supplier" :stats="$stats" />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>

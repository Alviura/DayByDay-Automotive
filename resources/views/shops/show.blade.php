<x-app-layout :title="$shop->name">

    @push('styles')
        <x-module.page-index-styles />
        @include('inventory.partials.page-styles')
        @include('returns.partials.page-styles')
    @endpush

    @php
        $activeTab = in_array(request('tab'), ['overview', 'stock', 'movement', 'activity'], true)
            ? request('tab')
            : 'overview';
    @endphp

    <div class="mi-page space-y-5" x-data="{ tab: '{{ $activeTab }}' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-store"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $shop->name }}</h1>
                        @if ($shop->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge"><i class="fas fa-barcode text-[0.55rem]"></i> {{ $shop->code }}</span>
                        <span>Retail location hub</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('shops.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @can('shops.manage')
                    <a href="{{ route('shops.edit', $shop) }}" class="mi-btn-orange"><i class="fas fa-pen text-xs"></i> Edit</a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">On Hand</p>
                    <p class="mi-kpi-value">{{ number_format($totals['on_hand'], 0) }}</p>
                    <p class="inv-kpi-sub">{{ number_format($totals['sku_count']) }} SKUs</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Stock Value</p>
                    <p class="mi-kpi-value orange" style="font-size:1.05rem">{{ number_format($totals['value'], 2) }}</p>
                    <p class="inv-kpi-sub">{{ number_format($totals['available'], 0) }} available</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            @can('sales.view')
                <div class="mi-kpi mi-kpi-purple">
                    <div>
                        <p class="mi-kpi-label">Today's Sales</p>
                        <p class="mi-kpi-value">{{ number_format($stats['completed_today']) }}</p>
                        <p class="inv-kpi-sub">KES {{ number_format($stats['today_total'], 0) }}</p>
                    </div>
                    <div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-amber">
                    <div>
                        <p class="mi-kpi-label">At Desk</p>
                        <p class="mi-kpi-value">{{ number_format($stats['held']) }}</p>
                        <p class="inv-kpi-sub">{{ number_format($shop->users_count) }} staff assigned</p>
                    </div>
                    <div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            @else
                <div class="mi-kpi mi-kpi-purple">
                    <div>
                        <p class="mi-kpi-label">Staff</p>
                        <p class="mi-kpi-value">{{ number_format($shop->users_count) }}</p>
                        <p class="inv-kpi-sub">Assigned users</p>
                    </div>
                    <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-amber">
                    <div>
                        <p class="mi-kpi-label">Low Stock</p>
                        <p class="mi-kpi-value">{{ number_format($totals['low_stock_count']) }}</p>
                        <p class="inv-kpi-sub">At reorder level</p>
                    </div>
                    <div class="mi-kpi-icon"><i class="fas fa-bell"></i></div>
                </div>
            @endcan
        </div>

        <div class="mi-tab-bar">
            <button type="button" @click="tab = 'overview'" :class="{ 'active': tab === 'overview' }">
                <i class="fas fa-circle-info"></i> Overview
            </button>
            <button type="button" @click="tab = 'stock'" :class="{ 'active': tab === 'stock' }">
                <i class="fas fa-boxes-stacked"></i> Stock
                @if ($totals['sku_count'] > 0)
                    <span class="mi-cat-badge !text-[0.62rem] !py-0">{{ $totals['sku_count'] }}</span>
                @endif
            </button>
            <button type="button" @click="tab = 'movement'" :class="{ 'active': tab === 'movement' }">
                <i class="fas fa-right-left"></i> Movement
            </button>
            <button type="button" @click="tab = 'activity'" :class="{ 'active': tab === 'activity' }">
                <i class="fas fa-bolt"></i> Activity
            </button>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">

                <div x-show="tab === 'overview'" x-transition>
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-circle-info text-gray-400 text-sm"></i>
                                <span class="text-sm font-semibold">Shop details</span>
                            </div>
                        </div>
                        <dl class="mi-detail-grid">
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Code</dt>
                                <dd class="mi-detail-value"><span class="mi-cat-badge">{{ $shop->code }}</span></dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-tag"></i> Name</dt>
                                <dd class="mi-detail-value">{{ $shop->name }}</dd>
                            </div>
                            <div class="mi-detail-item mi-span-full">
                                <dt class="mi-detail-label"><i class="fas fa-map-pin"></i> Address</dt>
                                <dd class="mi-detail-value">
                                    @if ($shop->address)
                                        <span class="mi-dest"><i class="fas fa-map-pin"></i>{{ $shop->address }}</span>
                                    @else
                                        <span class="mi-detail-empty">Not provided</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-phone"></i> Phone</dt>
                                <dd class="mi-detail-value">{{ $shop->phone ?: '—' }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-toggle-on"></i> Status</dt>
                                <dd class="mi-detail-value">
                                    @if ($shop->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-users text-gray-400 text-sm"></i>
                                <span class="text-sm font-semibold">Assigned staff</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $shop->users_count }} total</span>
                        </div>
                        @if ($shop->users_count > 0)
                            <div class="mi-table-wrap">
                                <table class="mi-table text-sm">
                                    <thead><tr><th>Name</th><th>Email</th></tr></thead>
                                    <tbody>
                                        @foreach ($shop->users as $user)
                                            <tr>
                                                <td class="font-medium">{{ $user->name }}</td>
                                                <td class="text-gray-500">{{ $user->email }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="mi-show-empty"><i class="fas fa-user-slash"></i><p>No staff assigned yet.</p></div>
                        @endif
                    </div>
                </div>

                <div x-show="tab === 'stock'" x-transition x-cloak>
                    @include('locations.partials.stock-tab', [
                        'location' => $shop,
                        'locationType' => $location_type,
                        'totals' => $totals,
                        'balances' => $balances,
                        'lowStock' => $lowStock,
                    ])
                </div>

                <div x-show="tab === 'movement'" x-transition x-cloak>
                    @include('locations.partials.movement-tab', [
                        'location' => $shop,
                        'locationType' => $location_type,
                        'movements' => $movements,
                    ])
                </div>

                <div x-show="tab === 'activity'" x-transition x-cloak>
                    @include('locations.partials.shop-activity-tab', compact(
                        'shop', 'stats', 'recentSales', 'heldSales', 'transfers', 'returns', 'adjustments'
                    ))
                </div>
            </div>

            <x-module.show-sidebar
                :model="$shop"
                :edit-url="route('shops.edit', $shop)"
                :index-url="route('shops.index')"
                edit-label="Edit Shop"
                index-label="All Shops"
                manage-permission="shops.manage"
            >
                <x-slot:footer>
                    <x-shop.show-sidebar-extra :shop="$shop" :totals="$totals" :location-type="$location_type" />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>

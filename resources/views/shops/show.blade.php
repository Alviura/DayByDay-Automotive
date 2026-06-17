<x-app-layout :title="$shop->name">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">

        {{-- Page header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-store"></i>
                </div>
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
                        <span class="mi-cat-badge">
                            <i class="fas fa-barcode text-[0.55rem]"></i>
                            {{ $shop->code }}
                        </span>
                        <span>Retail location overview</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('shops.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Back to List
                </a>
                @can('shops.manage')
                    <a href="{{ route('shops.edit', $shop) }}" class="mi-btn-orange">
                        <i class="fas fa-pen text-xs"></i>
                        Edit
                    </a>
                @endcan
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Status</p>
                    <p class="mi-kpi-value text-status">{{ $shop->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $shop->is_active ? 'circle-check' : 'pause-circle' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Assigned Staff</p>
                    <p class="mi-kpi-value">{{ number_format($shop->users_count) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Stock Rows</p>
                    <p class="mi-kpi-value">{{ number_format($shop->stock_balances_count) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Contact Info</p>
                    <p class="mi-kpi-value orange text-status">{{ filled($shop->address) || filled($shop->phone) ? 'Complete' : 'Partial' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-address-card"></i></div>
            </div>
        </div>

        {{-- Main content + sidebar --}}
        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">

                {{-- Overview --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-circle-info text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Overview</span>
                        </div>
                    </div>
                    <dl class="mi-detail-grid">
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Shop Code</dt>
                            <dd class="mi-detail-value">
                                <span class="mi-cat-badge">{{ $shop->code }}</span>
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-tag"></i> Shop Name</dt>
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
                            <dd class="mi-detail-value">
                                @if ($shop->phone)
                                    {{ $shop->phone }}
                                @else
                                    <span class="mi-detail-empty">Not provided</span>
                                @endif
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-toggle-on"></i> Availability</dt>
                            <dd class="mi-detail-value">
                                @if ($shop->is_active)
                                    <span class="mi-status-active">Active — open for assignments</span>
                                @else
                                    <span class="mi-status-inactive">Inactive — hidden from new use</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Assigned users --}}
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-users text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Assigned Staff</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $shop->users_count }} total</span>
                    </div>

                    @if ($shop->users_count > 0)
                        <div class="mi-table-wrap">
                            <table class="mi-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shop->users as $user)
                                        <tr>
                                            <td class="text-gray-400 font-medium">{{ $loop->iteration }}</td>
                                            <td>
                                                <p class="mi-pkg-name">{{ $user->name }}</p>
                                            </td>
                                            <td>
                                                <span class="text-gray-500 text-sm">{{ $user->email }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($shop->users_count > 10)
                            <div class="mi-card-foot">
                                <p class="text-xs text-gray-400">Showing 10 of {{ $shop->users_count }} assigned users.</p>
                            </div>
                        @endif
                    @else
                        <div class="mi-show-empty">
                            <i class="fas fa-user-slash"></i>
                            <p>No staff assigned to this shop yet.</p>
                        </div>
                    @endif
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
                    <x-shop.show-sidebar-extra :shop="$shop" />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>

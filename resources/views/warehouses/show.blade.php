<x-app-layout :title="$warehouse->name">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">

        {{-- Page header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $warehouse->name }}</h1>
                        @if ($warehouse->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge">
                            <i class="fas fa-barcode text-[0.55rem]"></i>
                            {{ $warehouse->code }}
                        </span>
                        <span>Storage location overview</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('warehouses.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Back to List
                </a>
                @can('warehouses.manage')
                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="mi-btn-orange">
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
                    <p class="mi-kpi-value text-status">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $warehouse->is_active ? 'circle-check' : 'pause-circle' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Assigned Staff</p>
                    <p class="mi-kpi-value">{{ number_format($warehouse->users_count) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Stock Rows</p>
                    <p class="mi-kpi-value">{{ number_format($warehouse->stock_balances_count) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Contact Info</p>
                    <p class="mi-kpi-value orange text-status">{{ filled($warehouse->address) || filled($warehouse->phone) ? 'Complete' : 'Partial' }}</p>
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
                            <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Warehouse Code</dt>
                            <dd class="mi-detail-value">
                                <span class="mi-cat-badge">{{ $warehouse->code }}</span>
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-tag"></i> Warehouse Name</dt>
                            <dd class="mi-detail-value">{{ $warehouse->name }}</dd>
                        </div>
                        <div class="mi-detail-item mi-span-full">
                            <dt class="mi-detail-label"><i class="fas fa-map-pin"></i> Address</dt>
                            <dd class="mi-detail-value">
                                @if ($warehouse->address)
                                    <span class="mi-dest"><i class="fas fa-map-pin"></i>{{ $warehouse->address }}</span>
                                @else
                                    <span class="mi-detail-empty">Not provided</span>
                                @endif
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-phone"></i> Phone</dt>
                            <dd class="mi-detail-value">
                                @if ($warehouse->phone)
                                    {{ $warehouse->phone }}
                                @else
                                    <span class="mi-detail-empty">Not provided</span>
                                @endif
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-toggle-on"></i> Availability</dt>
                            <dd class="mi-detail-value">
                                @if ($warehouse->is_active)
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
                        <span class="text-xs text-gray-400">{{ $warehouse->users_count }} total</span>
                    </div>

                    @if ($warehouse->users_count > 0)
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
                                    @foreach ($warehouse->users as $user)
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
                        @if ($warehouse->users_count > 10)
                            <div class="mi-card-foot">
                                <p class="text-xs text-gray-400">Showing 10 of {{ $warehouse->users_count }} assigned users.</p>
                            </div>
                        @endif
                    @else
                        <div class="mi-show-empty">
                            <i class="fas fa-user-slash"></i>
                            <p>No staff assigned to this warehouse yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <x-module.show-sidebar
                :model="$warehouse"
                :edit-url="route('warehouses.edit', $warehouse)"
                :index-url="route('warehouses.index')"
                edit-label="Edit Warehouse"
                index-label="All Warehouses"
                manage-permission="warehouses.manage"
            >
                <x-slot:footer>
                    <x-warehouse.show-sidebar-extra :warehouse="$warehouse" />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>

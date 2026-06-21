<x-app-layout :title="$supplier->name">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $supplier->name }}</h1>
                        @if ($supplier->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge">
                            <i class="fas fa-barcode text-[0.55rem]"></i>
                            {{ $supplier->code }}
                        </span>
                        <span>Vendor overview</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('suppliers.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Back to List
                </a>
                @can('suppliers.manage')
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="mi-btn-orange">
                        <i class="fas fa-pen text-xs"></i>
                        Edit
                    </a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Status</p>
                    <p class="mi-kpi-value text-status">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-{{ $supplier->is_active ? 'circle-check' : 'pause-circle' }}"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Lead Time</p>
                    <p class="mi-kpi-value text-status">{{ $supplier->lead_time_days ? $supplier->lead_time_days . 'd' : '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Rating</p>
                    <p class="mi-kpi-value">{{ $supplier->rating ? number_format($supplier->rating, 1) : '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-star"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Currency</p>
                    <p class="mi-kpi-value orange text-status">{{ $supplier->currency }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-circle-info text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Overview</span>
                        </div>
                    </div>
                    <dl class="mi-detail-grid">
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Supplier Code</dt>
                            <dd class="mi-detail-value"><span class="mi-cat-badge">{{ $supplier->code }}</span></dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-building"></i> Supplier Name</dt>
                            <dd class="mi-detail-value">{{ $supplier->name }}</dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-user"></i> Contact Person</dt>
                            <dd class="mi-detail-value">
                                {{ $supplier->contact_person ?? '—' }}
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-envelope"></i> Email</dt>
                            <dd class="mi-detail-value">
                                @if ($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-orange-600 hover:text-orange-700">{{ $supplier->email }}</a>
                                @else
                                    <span class="mi-detail-empty">Not provided</span>
                                @endif
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-phone"></i> Phone</dt>
                            <dd class="mi-detail-value">
                                {{ $supplier->phone ?? '—' }}
                            </dd>
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
                            <dt class="mi-detail-label"><i class="fas fa-truck"></i> Lead Time</dt>
                            <dd class="mi-detail-value">
                                {{ $supplier->lead_time_days ? $supplier->lead_time_days . ' days' : '—' }}
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-star"></i> Rating</dt>
                            <dd class="mi-detail-value">
                                {{ $supplier->rating ? number_format($supplier->rating, 2) . ' / 5' : '—' }}
                            </dd>
                        </div>
                        <div class="mi-detail-item">
                            <dt class="mi-detail-label"><i class="fas fa-toggle-on"></i> Availability</dt>
                            <dd class="mi-detail-value">
                                @if ($supplier->is_active)
                                    <span class="mi-status-active">Active — available for procurement</span>
                                @else
                                    <span class="mi-status-inactive">Inactive — hidden from new orders</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-folder-open text-gray-400 text-sm"></i>
                            <span class="text-sm font-semibold">Quotation Series History</span>
                        </div>
                    </div>
                    <div class="mi-show-empty">
                        <i class="fas fa-clock"></i>
                        <p>Quotation series and purchase orders will appear here once linked to this supplier.</p>
                    </div>
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
                    <x-supplier.show-sidebar-extra :supplier="$supplier" />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>

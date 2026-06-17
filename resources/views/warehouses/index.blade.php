<x-app-layout title="Warehouses">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: 'list' }">

        {{-- Page header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Warehouse Management</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Manage storage locations — name, code, address, phone and status.</p>
                </div>
            </div>
            @can('warehouses.manage')
                <a href="{{ route('warehouses.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i>
                    Add New Warehouse
                </a>
            @endcan
        </div>

        {{-- KPI cards (warehouse table fields only) --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Warehouses</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-warehouse"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Active</p>
                    <p class="mi-kpi-value">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Inactive</p>
                    <p class="mi-kpi-value">{{ number_format($stats['inactive']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-pause-circle"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">With Address</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['with_address']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-map-pin"></i></div>
            </div>
        </div>

        {{-- Filters & search --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Warehouse Filters</span>
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                    Toggle Filters
                    <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                </button>
            </div>

            <form method="GET" x-show="filtersOpen" x-transition>
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Name, code or address…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-barcode"></i> Warehouse Code</label>
                        <select name="code" class="mi-select">
                            <option value="">All codes</option>
                            @foreach ($codes as $code)
                                <option value="{{ $code }}" @selected(request('code') === $code)>{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-location-dot"></i> Address</label>
                        <select name="address" class="mi-select">
                            <option value="">All addresses</option>
                            @foreach ($addresses as $addr)
                                <option value="{{ $addr }}" @selected(request('address') === $addr)>{{ Str::limit($addr, 35) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-toggle-on"></i> Status</label>
                        <select name="status" class="mi-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="name" @selected(request('sort') === 'name')>Name (A–Z)</option>
                            <option value="code" @selected(request('sort') === 'code')>Code (A–Z)</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('warehouses.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Reset All
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $warehouses->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $warehouses->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $warehouses->total() }}</strong> warehouses
                </p>
                <div class="mi-view-toggle">
                    <button type="button" :class="{ 'active': viewMode === 'list' }" @click="viewMode = 'list'">
                        <i class="fas fa-list"></i>
                    </button>
                    <button type="button" :class="{ 'active': viewMode === 'grid' }" @click="viewMode = 'grid'">
                        <i class="fas fa-grip"></i>
                    </button>
                </div>
            </div>

            {{-- List view --}}
            <div x-show="viewMode === 'list'" class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warehouses as $warehouse)
                            <tr>
                                <td class="text-gray-400 font-medium">{{ $warehouses->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="mi-cat-badge">
                                        <i class="fas fa-barcode text-[0.55rem]"></i>
                                        {{ $warehouse->code }}
                                    </span>
                                </td>
                                <td>
                                    <p class="mi-pkg-name">{{ $warehouse->name }}</p>
                                </td>
                                <td>
                                    @if ($warehouse->address)
                                        <span class="mi-dest">
                                            <i class="fas fa-map-pin"></i>
                                            {{ Str::limit($warehouse->address, 40) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($warehouse->phone)
                                        <span class="flex items-center gap-1.5 text-gray-600">
                                            <i class="fas fa-phone text-[0.65rem] text-gray-300"></i>
                                            {{ $warehouse->phone }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($warehouse->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @can('warehouses.view')
                                            <a href="{{ route('warehouses.show', $warehouse) }}" class="mi-action view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('warehouses.manage')
                                            <a href="{{ route('warehouses.edit', $warehouse) }}" class="mi-action edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($warehouse->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="mi-action del" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!py-14 text-center text-gray-400">
                                    <i class="fas fa-warehouse mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No warehouses found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Grid view --}}
            <div x-show="viewMode === 'grid'" x-cloak class="mi-grid-wrap">
                @forelse ($warehouses as $warehouse)
                    <div class="mi-grid-item">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="mi-cat-badge">{{ $warehouse->code }}</span>
                                <p class="mi-pkg-name mt-2 truncate">{{ $warehouse->name }}</p>
                            </div>
                            @if ($warehouse->is_active)
                                <span class="mi-status-active flex-shrink-0">Active</span>
                            @else
                                <span class="mi-status-inactive flex-shrink-0">Inactive</span>
                            @endif
                        </div>
                        @if ($warehouse->address)
                            <p class="mi-dest mt-3 text-xs"><i class="fas fa-map-pin"></i>{{ Str::limit($warehouse->address, 45) }}</p>
                        @endif
                        @if ($warehouse->phone)
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-500">
                                <i class="fas fa-phone text-gray-300"></i>{{ $warehouse->phone }}
                            </p>
                        @endif
                        <div class="mt-3 flex items-center justify-end gap-1 border-t border-gray-50 pt-3">
                            @can('warehouses.view')
                                <a href="{{ route('warehouses.show', $warehouse) }}" class="mi-action view"><i class="fas fa-eye"></i></a>
                            @endcan
                            @can('warehouses.manage')
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400">No warehouses found.</div>
                @endforelse
            </div>

            @if ($warehouses->hasPages())
                <div class="mi-card-foot">
                    {{ $warehouses->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>

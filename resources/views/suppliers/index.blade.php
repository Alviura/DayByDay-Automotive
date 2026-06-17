<x-app-layout title="Suppliers">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: 'list' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Supplier Management</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Manage vendors — contact, country, currency, lead time and rating.</p>
                </div>
            </div>
            @can('suppliers.manage')
                <a href="{{ route('suppliers.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i>
                    Add New Supplier
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Suppliers</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
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
                    <p class="mi-kpi-label">Rated</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['rated']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-star"></i></div>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-sliders text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">Supplier Filters</span>
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
                                   placeholder="Name, code, contact…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-barcode"></i> Supplier Code</label>
                        <select name="code" class="mi-select">
                            <option value="">All codes</option>
                            @foreach ($codes as $code)
                                <option value="{{ $code }}" @selected(request('code') === $code)>{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-globe"></i> Country</label>
                        <select name="country" class="mi-select">
                            <option value="">All countries</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country }}" @selected(request('country') === $country)>{{ Str::limit($country, 35) }}</option>
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
                            <option value="rating" @selected(request('sort') === 'rating')>Highest rated</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Reset All
                    </a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $suppliers->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $suppliers->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $suppliers->total() }}</strong> suppliers
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

            <div x-show="viewMode === 'list'" class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Country</th>
                            <th>Currency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="text-gray-400 font-medium">{{ $suppliers->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="mi-cat-badge">
                                        <i class="fas fa-barcode text-[0.55rem]"></i>
                                        {{ $supplier->code }}
                                    </span>
                                </td>
                                <td><p class="mi-pkg-name">{{ $supplier->name }}</p></td>
                                <td>
                                    @if ($supplier->contact_person)
                                        <span class="text-gray-600 text-sm">{{ $supplier->contact_person }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($supplier->country)
                                        <span class="mi-dest"><i class="fas fa-globe"></i>{{ $supplier->country }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="mi-price text-sm">{{ $supplier->currency }}</span>
                                </td>
                                <td>
                                    @if ($supplier->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @can('suppliers.view')
                                            <a href="{{ route('suppliers.show', $supplier) }}" class="mi-action view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('suppliers.manage')
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="mi-action edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}?');">
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
                                <td colspan="8" class="!py-14 text-center text-gray-400">
                                    <i class="fas fa-truck mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No suppliers found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div x-show="viewMode === 'grid'" x-cloak class="mi-grid-wrap">
                @forelse ($suppliers as $supplier)
                    <div class="mi-grid-item">
                        <div class="mi-grid-item-head">
                            <div class="min-w-0">
                                <span class="mi-cat-badge">{{ $supplier->code }}</span>
                                <p class="mi-pkg-name mi-grid-item-name truncate">{{ $supplier->name }}</p>
                            </div>
                            @if ($supplier->is_active)
                                <span class="mi-status-active flex-shrink-0">Active</span>
                            @else
                                <span class="mi-status-inactive flex-shrink-0">Inactive</span>
                            @endif
                        </div>
                        <div class="mi-grid-item-meta">
                            @if ($supplier->contact_person)
                                <p class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <i class="fas fa-user text-gray-300"></i>{{ $supplier->contact_person }}
                                </p>
                            @endif
                            @if ($supplier->country)
                                <p class="mi-dest text-xs"><i class="fas fa-globe"></i>{{ $supplier->country }}</p>
                            @endif
                            <p class="flex items-center gap-1.5 text-xs text-gray-500">
                                <i class="fas fa-coins text-gray-300"></i>{{ $supplier->currency }}
                                @if ($supplier->rating)
                                    <span class="text-gray-300">·</span>
                                    <i class="fas fa-star text-amber-400 text-[0.6rem]"></i>{{ number_format($supplier->rating, 1) }}
                                @endif
                            </p>
                        </div>
                        <div class="mi-grid-item-actions">
                            @can('suppliers.view')
                                <a href="{{ route('suppliers.show', $supplier) }}" class="mi-action view"><i class="fas fa-eye"></i></a>
                            @endcan
                            @can('suppliers.manage')
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mi-action del" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400">No suppliers found.</div>
                @endforelse
            </div>

            @if ($suppliers->hasPages())
                <div class="mi-card-foot">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>

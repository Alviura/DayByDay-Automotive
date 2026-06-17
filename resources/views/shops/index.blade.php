<x-app-layout title="Shops">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: 'list' }">

        {{-- Page header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Shop Management</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Manage retail locations — name, code, address, phone and status.</p>
                </div>
            </div>
            @can('shops.manage')
                <a href="{{ route('shops.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i>
                    Add New Shop
                </a>
            @endcan
        </div>

        {{-- KPI cards --}}
        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Shops</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-store"></i></div>
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
                    <span class="text-sm font-semibold">Shop Filters</span>
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
                        <label class="mi-field-label"><i class="fas fa-barcode"></i> Shop Code</label>
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
                    <a href="{{ route('shops.index') }}" class="mi-btn-ghost">
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
                    Showing <strong class="text-gray-700">{{ $shops->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $shops->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $shops->total() }}</strong> shops
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
                        @forelse ($shops as $shop)
                            <tr>
                                <td class="text-gray-400 font-medium">{{ $shops->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="mi-cat-badge">
                                        <i class="fas fa-barcode text-[0.55rem]"></i>
                                        {{ $shop->code }}
                                    </span>
                                </td>
                                <td>
                                    <p class="mi-pkg-name">{{ $shop->name }}</p>
                                </td>
                                <td>
                                    @if ($shop->address)
                                        <span class="mi-dest">
                                            <i class="fas fa-map-pin"></i>
                                            {{ Str::limit($shop->address, 40) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($shop->phone)
                                        <span class="flex items-center gap-1.5 text-gray-600">
                                            <i class="fas fa-phone text-[0.65rem] text-gray-300"></i>
                                            {{ $shop->phone }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($shop->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @can('shops.view')
                                            <a href="{{ route('shops.show', $shop) }}" class="mi-action view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('shops.manage')
                                            <a href="{{ route('shops.edit', $shop) }}" class="mi-action edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('shops.destroy', $shop) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($shop->name) }}?');">
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
                                    <i class="fas fa-store mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No shops found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Grid view --}}
            <div x-show="viewMode === 'grid'" x-cloak class="mi-grid-wrap">
                @forelse ($shops as $shop)
                    <div class="mi-grid-item">
                        <div class="mi-grid-item-head">
                            <div class="min-w-0">
                                <span class="mi-cat-badge">{{ $shop->code }}</span>
                                <p class="mi-pkg-name mi-grid-item-name truncate">{{ $shop->name }}</p>
                            </div>
                            @if ($shop->is_active)
                                <span class="mi-status-active flex-shrink-0">Active</span>
                            @else
                                <span class="mi-status-inactive flex-shrink-0">Inactive</span>
                            @endif
                        </div>
                        @if ($shop->address || $shop->phone)
                            <div class="mi-grid-item-meta">
                                @if ($shop->address)
                                    <p class="mi-dest text-xs"><i class="fas fa-map-pin"></i>{{ Str::limit($shop->address, 45) }}</p>
                                @endif
                                @if ($shop->phone)
                                    <p class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <i class="fas fa-phone text-gray-300"></i>{{ $shop->phone }}
                                    </p>
                                @endif
                            </div>
                        @endif
                        <div class="mi-grid-item-actions">
                            @can('shops.view')
                                <a href="{{ route('shops.show', $shop) }}" class="mi-action view"><i class="fas fa-eye"></i></a>
                            @endcan
                            @can('shops.manage')
                                <a href="{{ route('shops.edit', $shop) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('shops.destroy', $shop) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($shop->name) }}?');">
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
                    <div class="col-span-full py-12 text-center text-gray-400">No shops found.</div>
                @endforelse
            </div>

            @if ($shops->hasPages())
                <div class="mi-card-foot">
                    {{ $shops->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>

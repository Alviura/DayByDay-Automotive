@php
    $catalogUrl = fn (string $tab, array $extra = []) => route('product-catalog.index', array_merge(['view' => $tab], $extra));
@endphp

<x-app-layout title="Categories, Names & Units">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    @php
        $defaultViewMode = $view === 'categories'
            ? (request('display') === 'list' ? 'list' : 'tree')
            : 'list';
    @endphp

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: @js($defaultViewMode) }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Categories, Names & Units</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Product categories, generic part names, and units of measure.</p>
                </div>
            </div>
            @can('master-data.manage')
                @if ($view === 'categories')
                    <a href="{{ route('categories.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i>
                        Add Category
                    </a>
                @elseif ($view === 'names')
                    <a href="{{ route('product-names.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i>
                        Add Product Name
                    </a>
                @else
                    <a href="{{ route('units.create') }}" class="mi-btn-orange">
                        <i class="fas fa-plus text-xs"></i>
                        Add Unit
                    </a>
                @endif
            @endcan
        </div>

        <div class="mi-tab-bar">
            <a href="{{ $catalogUrl('categories') }}" class="{{ $view === 'categories' ? 'active' : '' }}">
                <i class="fas fa-folder-tree"></i> Categories
            </a>
            <a href="{{ $catalogUrl('names') }}" class="{{ $view === 'names' ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Product Names
            </a>
            <a href="{{ $catalogUrl('units') }}" class="{{ $view === 'units' ? 'active' : '' }}">
                <i class="fas fa-ruler-combined"></i> Units
            </a>
        </div>

        @if ($view === 'categories')
            @include('product-catalog.partials.categories')
        @elseif ($view === 'names')
            <div class="mi-kpi-row">
                <div class="mi-kpi mi-kpi-purple">
                    <div><p class="mi-kpi-label">Total Names</p><p class="mi-kpi-value">{{ number_format($stats['total']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-tags"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-green">
                    <div><p class="mi-kpi-label">Active</p><p class="mi-kpi-value">{{ number_format($stats['active']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-amber">
                    <div><p class="mi-kpi-label">Inactive</p><p class="mi-kpi-value">{{ number_format($stats['inactive']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-pause-circle"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-orange">
                    <div><p class="mi-kpi-label">Total Units</p><p class="mi-kpi-value orange">{{ number_format($stats['related']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-ruler-combined"></i></div>
                </div>
            </div>

            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-sliders text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Filters</span>
                    </div>
                    <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                        Toggle Filters
                        <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                    </button>
                </div>
                <form method="GET" x-show="filtersOpen" x-transition>
                    <input type="hidden" name="view" value="names">
                    <div class="mi-filter-grid">
                        <div class="mi-filter-field">
                            <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                            <div class="mi-input-wrap">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name…" class="mi-input">
                            </div>
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
                            </select>
                        </div>
                    </div>
                    <div class="mi-filter-actions">
                        <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply Filters</button>
                        <a href="{{ $catalogUrl('names') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset All</a>
                    </div>
                </form>
            </div>

            <div class="mi-card">
                <div class="mi-card-head">
                    <p class="text-sm text-gray-500">
                        Showing <strong class="text-gray-700">{{ $productNames->firstItem() ?? 0 }}</strong>
                        to <strong class="text-gray-700">{{ $productNames->lastItem() ?? 0 }}</strong>
                        of <strong class="text-gray-700">{{ $productNames->total() }}</strong> product names
                    </p>
                    <div class="mi-view-toggle">
                        <button type="button" :class="{ 'active': viewMode === 'list' }" @click="viewMode = 'list'"><i class="fas fa-list"></i></button>
                        <button type="button" :class="{ 'active': viewMode === 'grid' }" @click="viewMode = 'grid'"><i class="fas fa-grip"></i></button>
                    </div>
                </div>
                <div x-show="viewMode === 'list'" class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>#</th><th>Product Name</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            @forelse ($productNames as $productName)
                                <tr>
                                    <td class="text-gray-400 font-medium">{{ $productNames->firstItem() + $loop->index }}</td>
                                    <td><p class="mi-pkg-name">{{ $productName->name }}</p></td>
                                    <td>
                                        @if ($productName->is_active)<span class="mi-status-active">Active</span>
                                        @else<span class="mi-status-inactive">Inactive</span>@endif
                                    </td>
                                    <td>
                                        @can('master-data.manage')
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('product-names.edit', $productName) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                                                <form action="{{ route('product-names.destroy', $productName) }}" method="POST" class="inline" data-confirm="Delete {{ addslashes($productName->name) }}?" data-confirm-variant="danger">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="mi-action del"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="!py-14 text-center text-gray-400">No product names found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($productNames->hasPages())<div class="mi-card-foot">{{ $productNames->links() }}</div>@endif
            </div>
        @else
            <div class="mi-kpi-row">
                <div class="mi-kpi mi-kpi-purple">
                    <div><p class="mi-kpi-label">Total Units</p><p class="mi-kpi-value">{{ number_format($stats['total']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-ruler-combined"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-green">
                    <div><p class="mi-kpi-label">Active</p><p class="mi-kpi-value">{{ number_format($stats['active']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-circle-check"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-amber">
                    <div><p class="mi-kpi-label">Inactive</p><p class="mi-kpi-value">{{ number_format($stats['inactive']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-pause-circle"></i></div>
                </div>
                <div class="mi-kpi mi-kpi-orange">
                    <div><p class="mi-kpi-label">Product Names</p><p class="mi-kpi-value orange">{{ number_format($stats['related']) }}</p></div>
                    <div class="mi-kpi-icon"><i class="fas fa-tags"></i></div>
                </div>
            </div>

            <div class="mi-card">
                <div class="mi-card-head">
                    <div class="flex items-center gap-2 text-gray-700">
                        <i class="fas fa-sliders text-gray-400 text-sm"></i>
                        <span class="text-sm font-semibold">Filters</span>
                    </div>
                    <button type="button" @click="filtersOpen = !filtersOpen" class="mi-btn-toggle">
                        Toggle Filters
                        <i class="fas fa-chevron-down text-[0.55rem] transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                    </button>
                </div>
                <form method="GET" x-show="filtersOpen" x-transition>
                    <input type="hidden" name="view" value="units">
                    <div class="mi-filter-grid">
                        <div class="mi-filter-field">
                            <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                            <div class="mi-input-wrap">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or abbreviation…" class="mi-input">
                            </div>
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
                                <option value="abbreviation" @selected(request('sort') === 'abbreviation')>Abbreviation (A–Z)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mi-filter-actions">
                        <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply Filters</button>
                        <a href="{{ $catalogUrl('units') }}" class="mi-btn-ghost"><i class="fas fa-rotate-left text-xs"></i> Reset All</a>
                    </div>
                </form>
            </div>

            <div class="mi-card">
                <div class="mi-card-head">
                    <p class="text-sm text-gray-500">
                        Showing <strong class="text-gray-700">{{ $units->firstItem() ?? 0 }}</strong>
                        to <strong class="text-gray-700">{{ $units->lastItem() ?? 0 }}</strong>
                        of <strong class="text-gray-700">{{ $units->total() }}</strong> units
                    </p>
                    <div class="mi-view-toggle">
                        <button type="button" :class="{ 'active': viewMode === 'list' }" @click="viewMode = 'list'"><i class="fas fa-list"></i></button>
                        <button type="button" :class="{ 'active': viewMode === 'grid' }" @click="viewMode = 'grid'"><i class="fas fa-grip"></i></button>
                    </div>
                </div>
                <div x-show="viewMode === 'list'" class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>#</th><th>Unit Name</th><th>Abbreviation</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            @forelse ($units as $unit)
                                <tr>
                                    <td class="text-gray-400 font-medium">{{ $units->firstItem() + $loop->index }}</td>
                                    <td><p class="mi-pkg-name">{{ $unit->name }}</p></td>
                                    <td>@if ($unit->abbreviation)<span class="mi-cat-badge">{{ $unit->abbreviation }}</span>@else<span class="text-gray-300">—</span>@endif</td>
                                    <td>
                                        @if ($unit->is_active)<span class="mi-status-active">Active</span>
                                        @else<span class="mi-status-inactive">Inactive</span>@endif
                                    </td>
                                    <td>
                                        @can('master-data.manage')
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('units.edit', $unit) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="inline" data-confirm="Delete {{ addslashes($unit->name) }}?" data-confirm-variant="danger">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="mi-action del"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="!py-14 text-center text-gray-400">No units found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($units->hasPages())<div class="mi-card-foot">{{ $units->links() }}</div>@endif
            </div>
        @endif
    </div>
</x-app-layout>

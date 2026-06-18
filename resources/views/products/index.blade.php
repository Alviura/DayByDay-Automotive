<x-app-layout title="Products">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: 'list' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-car-side"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Product Catalogue</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Manage parts — part numbers, fitment, pricing, and barcodes.</p>
                </div>
            </div>
            @can('products.create')
                <a href="{{ route('products.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i>
                    Add Product
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Products</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-car-side"></i></div>
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
                    <p class="mi-kpi-label">With Barcode</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['with_barcode']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-qrcode"></i></div>
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
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-magnifying-glass"></i> Search</label>
                        <div class="mi-input-wrap">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Part number, name, barcode…" class="mi-input">
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
                        <label class="mi-field-label"><i class="fas fa-folder-tree"></i> Category</label>
                        <select name="category_id" class="mi-select">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->parent ? $category->parent->name.' › ' : '' }}{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-truck"></i> Supplier</label>
                        <select name="supplier_id" class="mi-select">
                            <option value="">All suppliers</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-car-side"></i> Vehicle Make</label>
                        <select name="vehicle_make_id" class="mi-select">
                            <option value="">All makes</option>
                            @foreach ($makes as $make)
                                <option value="{{ $make->id }}" @selected(request('vehicle_make_id') == $make->id)>
                                    {{ $make->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-arrow-down-wide-short"></i> Sort By</label>
                        <select name="sort" class="mi-select">
                            <option value="">Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                            <option value="part_number" @selected(request('sort') === 'part_number')>Part number (A–Z)</option>
                            <option value="name" @selected(request('sort') === 'name')>Name (A–Z)</option>
                            <option value="price" @selected(request('sort') === 'price')>Selling price (high–low)</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('products.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Reset All
                    </a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $products->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $products->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $products->total() }}</strong> products
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
                            <th>Part Number</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="text-gray-400 font-medium">{{ $products->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="mi-cat-badge hover:bg-orange-50">
                                        {{ $product->part_number }}
                                    </a>
                                </td>
                                <td>
                                    <p class="mi-pkg-name">{{ $product->name }}</p>
                                    @if ($product->vehicleMake)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $product->vehicleMake->name }}</p>
                                    @endif
                                </td>
                                <td>
                                    @if ($product->category)
                                        <span class="text-sm text-gray-600">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($product->unit)
                                        <span class="text-sm text-gray-600">{{ $product->unit->abbreviation ?? $product->unit->name }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="font-medium text-gray-800">{{ number_format($product->selling_price, 2) }}</td>
                                <td>
                                    @if ($product->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('products.show', $product) }}" class="mi-action view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('products.edit')
                                            <a href="{{ route('products.edit', $product) }}" class="mi-action edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endcan
                                        @can('products.archive')
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($product->name) }}?');">
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
                                    <i class="fas fa-car-side mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No products found</p>
                                    @can('products.create')
                                        <a href="{{ route('products.create') }}" class="mt-2 inline-block text-sm text-orange-600 hover:text-orange-700">Add your first product</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div x-show="viewMode === 'grid'" x-cloak class="mi-grid-wrap">
                @forelse ($products as $product)
                    <div class="mi-grid-item">
                        <div class="mi-grid-item-head">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400 font-mono">{{ $product->part_number }}</p>
                                <p class="mi-pkg-name truncate">{{ $product->name }}</p>
                                <p class="text-sm font-semibold text-gray-800 mt-1">{{ number_format($product->selling_price, 2) }}</p>
                            </div>
                            @if ($product->is_active)
                                <span class="mi-status-active flex-shrink-0">Active</span>
                            @else
                                <span class="mi-status-inactive flex-shrink-0">Inactive</span>
                            @endif
                        </div>
                        <div class="mi-grid-item-actions">
                            <a href="{{ route('products.show', $product) }}" class="mi-action view"><i class="fas fa-eye"></i></a>
                            @can('products.edit')
                                <a href="{{ route('products.edit', $product) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                            @endcan
                            @can('products.archive')
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($product->name) }}?');">
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
                    <div class="col-span-full py-12 text-center text-gray-400">No products found.</div>
                @endforelse
            </div>

            @if ($products->hasPages())
                <div class="mi-card-foot">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

<x-app-layout title="Vehicle Models">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true, viewMode: 'list' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Vehicle Models</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Manage models under each make for product fitment.</p>
                </div>
            </div>
            @can('master-data.manage')
                <a href="{{ route('vehicle-models.create', request()->only('vehicle_make_id')) }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i>
                    Add Model
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Total Models</p>
                    <p class="mi-kpi-value">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-car"></i></div>
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
                    <p class="mi-kpi-label">Makes</p>
                    <p class="mi-kpi-value orange">{{ number_format($stats['makes']) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-car-side"></i></div>
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
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Model or make…" class="mi-input">
                        </div>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label"><i class="fas fa-car-side"></i> Make</label>
                        <select name="vehicle_make_id" class="mi-select">
                            <option value="">All makes</option>
                            @foreach ($makes as $id => $name)
                                <option value="{{ $id }}" @selected(request('vehicle_make_id') == $id)>{{ $name }}</option>
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
                            <option value="name" @selected(request('sort') === 'name')>Model (A–Z)</option>
                            <option value="make" @selected(request('sort') === 'make')>Make (A–Z)</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions">
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('vehicle-models.index') }}" class="mi-btn-ghost">
                        <i class="fas fa-rotate-left text-xs"></i>
                        Reset All
                    </a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="text-sm text-gray-500">
                    Showing <strong class="text-gray-700">{{ $vehicleModels->firstItem() ?? 0 }}</strong>
                    to <strong class="text-gray-700">{{ $vehicleModels->lastItem() ?? 0 }}</strong>
                    of <strong class="text-gray-700">{{ $vehicleModels->total() }}</strong> models
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
                            <th>Make</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicleModels as $model)
                            <tr>
                                <td class="text-gray-400 font-medium">{{ $vehicleModels->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="mi-cat-badge">{{ $model->make->name }}</span>
                                </td>
                                <td><p class="mi-pkg-name">{{ $model->name }}</p></td>
                                <td>
                                    @if ($model->is_active)
                                        <span class="mi-status-active">Active</span>
                                    @else
                                        <span class="mi-status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @can('master-data.manage')
                                            <a href="{{ route('vehicle-models.edit', $model) }}" class="mi-action edit" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('vehicle-models.destroy', $model) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete {{ addslashes($model->make->name . ' ' . $model->name) }}?');">
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
                                <td colspan="5" class="!py-14 text-center text-gray-400">
                                    <i class="fas fa-car mb-2 block text-3xl text-gray-200"></i>
                                    <p class="font-medium text-gray-600">No vehicle models found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div x-show="viewMode === 'grid'" x-cloak class="mi-grid-wrap">
                @forelse ($vehicleModels as $model)
                    <div class="mi-grid-item">
                        <div class="mi-grid-item-head">
                            <div class="min-w-0">
                                <span class="mi-cat-badge">{{ $model->make->name }}</span>
                                <p class="mi-pkg-name mi-grid-item-name truncate">{{ $model->name }}</p>
                            </div>
                            @if ($model->is_active)
                                <span class="mi-status-active flex-shrink-0">Active</span>
                            @else
                                <span class="mi-status-inactive flex-shrink-0">Inactive</span>
                            @endif
                        </div>
                        <div class="mi-grid-item-actions">
                            @can('master-data.manage')
                                <a href="{{ route('vehicle-models.edit', $model) }}" class="mi-action edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('vehicle-models.destroy', $model) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($model->make->name . ' ' . $model->name) }}?');">
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
                    <div class="col-span-full py-12 text-center text-gray-400">No vehicle models found.</div>
                @endforelse
            </div>

            @if ($vehicleModels->hasPages())
                <div class="mi-card-foot">{{ $vehicleModels->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

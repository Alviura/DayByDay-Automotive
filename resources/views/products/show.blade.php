<x-app-layout :title="$product->part_number">

    @push('styles')
        <x-module.page-index-styles />
        @include('inventory.partials.page-styles')
    @endpush

    @php
        $activeTab = in_array(request('tab'), ['overview', 'stock', 'movement', 'procurement'], true)
            ? request('tab')
            : 'overview';
    @endphp

    <div class="mi-page space-y-5" x-data="{ tab: '{{ $activeTab }}' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon">
                    <i class="fas fa-car-side"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $product->productName?->name ?? $product->name }}</h1>
                        @if ($product->is_active)
                            <span class="mi-status-active">Active</span>
                        @else
                            <span class="mi-status-inactive">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="mi-cat-badge">
                            <i class="fas fa-barcode text-[0.55rem]"></i>
                            {{ $product->part_number }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('products.index') }}" class="mi-btn-ghost">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Back to List
                </a>
                @can('products.edit')
                    <a href="{{ route('products.edit', $product) }}" class="mi-btn-orange">
                        <i class="fas fa-pen text-xs"></i>
                        Edit
                    </a>
                @endcan
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Min Selling Price</p>
                    <p class="mi-kpi-value text-status">{{ number_format($product->min_selling_price, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-tag"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Max Selling Price</p>
                    <p class="mi-kpi-value text-status">{{ number_format($product->max_selling_price, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-tags"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Cost Price</p>
                    <p class="mi-kpi-value text-status">{{ number_format($product->cost_price, 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Reorder Level</p>
                    <p class="mi-kpi-value orange text-status">{{ $product->reorder_level ?: '—' }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-bell"></i></div>
            </div>
        </div>

        <div class="mi-tab-bar">
            <button type="button" @click="tab = 'overview'" :class="{ 'active': tab === 'overview' }">
                <i class="fas fa-circle-info"></i> Overview
            </button>
            <button type="button" @click="tab = 'stock'" :class="{ 'active': tab === 'stock' }">
                <i class="fas fa-boxes-stacked"></i> Stock Balances
                @if ($totals['on_hand'] > 0)
                    <span class="mi-cat-badge !text-[0.62rem] !py-0">{{ number_format($totals['on_hand'], 0) }}</span>
                @endif
            </button>
            <button type="button" @click="tab = 'movement'" :class="{ 'active': tab === 'movement' }">
                <i class="fas fa-right-left"></i> Movement
            </button>
            <button type="button" @click="tab = 'procurement'" :class="{ 'active': tab === 'procurement' }">
                <i class="fas fa-file-invoice-dollar"></i> Procurement
            </button>
        </div>

        <div class="mi-form-split">
            <div class="mi-form-main space-y-5">

                <div x-show="tab === 'overview'" x-transition>
                    <div class="mi-card">
                        <div class="mi-card-head">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fas fa-circle-info text-gray-400 text-sm"></i>
                                <span class="text-sm font-semibold">Product Details</span>
                            </div>
                        </div>
                        <dl class="mi-detail-grid">
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-barcode"></i> Part Number</dt>
                                <dd class="mi-detail-value"><span class="mi-cat-badge">{{ $product->part_number }}</span></dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-tags"></i> Product Name</dt>
                                <dd class="mi-detail-value">{{ $product->productName?->name ?? '—' }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-tag"></i> Selling Price Range</dt>
                                <dd class="mi-detail-value">{{ $product->sellingPriceLabel() }}</dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-folder-tree"></i> Category</dt>
                                <dd class="mi-detail-value">
                                    @if ($product->category)
                                        {{ $product->category->parent ? $product->category->parent->name.' › ' : '' }}{{ $product->category->name }}
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-ruler-combined"></i> Unit</dt>
                                <dd class="mi-detail-value">
                                    @if ($product->unit)
                                        {{ $product->unit->name }}{{ $product->unit->abbreviation ? ' ('.$product->unit->abbreviation.')' : '' }}
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div class="mi-detail-item">
                                <dt class="mi-detail-label"><i class="fas fa-car-side"></i> Primary Fitment</dt>
                                <dd class="mi-detail-value">
                                    @if ($product->vehicleMake)
                                        {{ $product->vehicleMake->name }}{{ $product->vehicleModel ? ' '.$product->vehicleModel->name : '' }}
                                    @else
                                        Universal / N/A
                                    @endif
                                </dd>
                            </div>
                            <div class="mi-detail-item mi-span-full">
                                <dt class="mi-detail-label"><i class="fas fa-car"></i> Additional Fitment</dt>
                                <dd class="mi-detail-value">
                                    @if ($product->fitmentModels->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($product->fitmentModels as $model)
                                                <span class="mi-cat-badge">{{ $model->make->name }} {{ $model->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="mi-detail-empty">No additional models</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($product->description)
                                <div class="mi-detail-item mi-span-full">
                                    <dt class="mi-detail-label"><i class="fas fa-align-left"></i> Description</dt>
                                    <dd class="mi-detail-value">{{ $product->description }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div x-show="tab === 'stock'" x-cloak x-transition>
                    @include('products.partials.stock-tab')
                </div>

                <div x-show="tab === 'movement'" x-cloak x-transition>
                    @include('products.partials.movement-tab')
                </div>

                <div x-show="tab === 'procurement'" x-cloak x-transition>
                    @include('products.partials.procurement-tab')
                </div>
            </div>

            <x-module.show-sidebar
                :model="$product"
                :edit-url="route('products.edit', $product)"
                :index-url="route('products.index')"
                edit-label="Edit Product"
                index-label="All Products"
                manage-permission="products.edit"
            >
                <x-slot:footer>
                    <x-product.show-sidebar-extra :product="$product" />
                </x-slot:footer>
            </x-module.show-sidebar>
        </div>
    </div>
</x-app-layout>

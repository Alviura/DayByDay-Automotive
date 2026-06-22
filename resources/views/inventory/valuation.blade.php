@php
    $whShare = $grandTotal > 0 ? round($warehouseTotal / $grandTotal * 100) : 0;
    $shShare = $grandTotal > 0 ? 100 - $whShare : 0;
    $detailType = $detailLocation instanceof \App\Models\Warehouse ? 'warehouse' : 'shop';
@endphp

<x-app-layout title="Inventory Valuation">

    @push('styles')
        <x-module.page-index-styles />
        @include('inventory.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ typeFilter: '', hideEmpty: true, search: '' }">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-coins"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Inventory Valuation</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Stock value by location — quantity on hand × weighted average cost.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.index') }}" class="mi-btn-ghost"><i class="fas fa-boxes-stacked text-xs"></i> Balances</a>
                <a href="{{ route('inventory.movements') }}" class="mi-btn-ghost"><i class="fas fa-right-left text-xs"></i> Movements</a>
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Grand Total Value</p>
                    <p class="mi-kpi-value orange" style="font-size:1.15rem">{{ number_format($grandTotal, 2) }}</p>
                    <p class="inv-kpi-sub">KES · {{ $activeLocations }} active {{ str('location')->plural($activeLocations) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">Warehouse Value</p>
                    <p class="mi-kpi-value">{{ number_format($warehouseTotal, 2) }}</p>
                    <p class="inv-kpi-sub">{{ $whShare }}% of total</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-warehouse"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Shop Value</p>
                    <p class="mi-kpi-value">{{ number_format($shopTotal, 2) }}</p>
                    <p class="inv-kpi-sub">{{ $shShare }}% of total</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-store"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">Units · SKUs</p>
                    <p class="mi-kpi-value">{{ number_format($grandUnits, 0) }}</p>
                    <p class="inv-kpi-sub">{{ number_format($uniqueSkus) }} unique products</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
            </div>
        </div>

        @if ($grandTotal > 0)
            <div class="inv-val-split-card">
                <div class="inv-val-split-head">
                    <p class="inv-val-split-title">Value distribution</p>
                    <div class="inv-val-split-legend">
                        <span><i class="inv-val-split-wh"></i> Warehouses {{ number_format($warehouseTotal, 2) }} ({{ $whShare }}%)</span>
                        <span><i class="inv-val-split-sh"></i> Shops {{ number_format($shopTotal, 2) }} ({{ $shShare }}%)</span>
                    </div>
                </div>
                <div class="inv-val-split-bar">
                    @if ($warehouseTotal > 0)
                        <span class="inv-val-split-wh" style="width:{{ max(2, $whShare) }}%"></span>
                    @endif
                    @if ($shopTotal > 0)
                        <span class="inv-val-split-sh" style="width:{{ max(2, $shShare) }}%"></span>
                    @endif
                </div>
            </div>
        @endif

        <div class="mi-form-split">
            <div class="space-y-5 min-w-0">

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="inv-section-title"><i class="fas fa-map-location-dot"></i> Value by Location</p>
                            <p class="inv-section-sub">Click a row to drill into SKU-level valuation</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 px-4 py-3 flex flex-wrap items-end gap-3">
                        <div class="mi-filter-field flex-1 min-w-[10rem]">
                            <label class="mi-field-label">Search location</label>
                            <input type="text" x-model="search" class="mi-input" placeholder="Name or code…">
                        </div>
                        <div class="mi-filter-field min-w-[8rem]">
                            <label class="mi-field-label">Type</label>
                            <select x-model="typeFilter" class="mi-select">
                                <option value="">All types</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="shop">Shop</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-600 pb-2 cursor-pointer select-none">
                            <input type="checkbox" x-model="hideEmpty" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            Hide empty locations
                        </label>
                    </div>

                    <div class="mi-table-wrap">
                        <table class="mi-table inv-val-table">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>SKUs</th>
                                    <th>Units</th>
                                    <th>Share</th>
                                    <th class="text-right">Value (KES)</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($locations as $row)
                                    @php
                                        $share = $grandTotal > 0 ? round($row['total_value'] / $grandTotal * 100) : 0;
                                        $isWh = $row['type'] === 'Warehouse';
                                        $locType = strtolower($row['type']);
                                        $isActive = $detailLocation
                                            && $detailLocation->id === $row['location']->id
                                            && $detailType === $locType;
                                        $detailUrl = route('inventory.valuation', [
                                            'location_type' => $locType,
                                            'location_id' => $row['location']->id,
                                        ]);
                                    @endphp
                                    <tr
                                        class="inv-val-row {{ $isActive ? 'is-active' : '' }} {{ $row['total_value'] <= 0 ? 'is-empty' : '' }}"
                                        data-search="{{ strtolower($row['location']->name.' '.($row['location']->code ?? '')) }}"
                                        onclick="window.location='{{ $detailUrl }}'"
                                        x-show="(!hideEmpty || {{ (float) $row['total_value'] }} > 0)
                                            && (!typeFilter || typeFilter === '{{ $locType }}')
                                            && (!search || $el.dataset.search.includes(search.toLowerCase()))"
                                    >
                                        <td>
                                            <div class="inv-val-loc-cell">
                                                <span class="inv-val-loc-icon {{ $isWh ? 'inv-val-loc-icon-wh' : 'inv-val-loc-icon-sh' }}">
                                                    <i class="fas fa-{{ $isWh ? 'warehouse' : 'store' }}"></i>
                                                </span>
                                                <div>
                                                    <p class="inv-val-loc-name">{{ $row['location']->name }}</p>
                                                    @if ($row['location']->code ?? null)
                                                        <p class="inv-val-loc-code">{{ $row['location']->code }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="inv-badge {{ $isWh ? 'inv-badge-teal' : 'inv-badge-indigo' }}">{{ $row['type'] }}</span>
                                        </td>
                                        <td>{{ number_format($row['sku_count']) }}</td>
                                        <td class="font-medium">{{ number_format($row['total_units'], 0) }}</td>
                                        <td class="min-w-[9rem]">
                                            @if ($row['total_value'] > 0)
                                                <div class="flex items-center gap-2">
                                                    <div class="inv-val-bar-track flex-1">
                                                        <div class="inv-val-bar-fill" style="width: {{ max(4, $share) }}%"></div>
                                                    </div>
                                                    <span class="inv-val-share-pill">{{ $share }}%</span>
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-sm">—</span>
                                            @endif
                                        </td>
                                        <td class="font-bold text-orange-700 text-right">{{ number_format($row['total_value'], 2) }}</td>
                                        <td class="text-gray-300"><i class="fas fa-chevron-right text-xs"></i></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($grandTotal > 0)
                                <tfoot>
                                    <tr class="bg-stone-50 font-semibold">
                                        <td colspan="5" class="text-right text-gray-600">Grand total</td>
                                        <td class="text-right text-orange-700">{{ number_format($grandTotal, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    <div class="inv-index-hint">
                        <i class="fas fa-circle-info"></i>
                        <span>Locations sorted by value. Share bars compare each site to the organisation total.</span>
                    </div>
                </div>

                @if ($detail && $detailLocation)
                    @php
                        $isDetailWh = $detailLocation instanceof \App\Models\Warehouse;
                        $detailShare = $grandTotal > 0 ? round($detail['total_value'] / $grandTotal * 100) : 0;
                    @endphp
                    <div class="mi-card overflow-hidden" id="valuation-detail">
                        <div class="inv-val-detail-hero">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inv-val-loc-icon {{ $isDetailWh ? 'inv-val-loc-icon-wh' : 'inv-val-loc-icon-sh' }}">
                                        <i class="fas fa-{{ $isDetailWh ? 'warehouse' : 'store' }}"></i>
                                    </span>
                                    <div>
                                        <p class="inv-val-detail-hero-title">{{ $detailLocation->name }}</p>
                                        <p class="inv-val-detail-hero-sub">
                                            {{ $isDetailWh ? 'Warehouse' : 'Shop' }}
                                            @if ($detailLocation->code ?? null)
                                                · {{ $detailLocation->code }}
                                            @endif
                                            · {{ $detailShare }}% of total value
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="inv-val-detail-kpis">
                                <div class="inv-val-detail-kpi">
                                    <p class="inv-val-detail-kpi-label">SKUs</p>
                                    <p class="inv-val-detail-kpi-value">{{ number_format($detail['sku_count']) }}</p>
                                </div>
                                <div class="inv-val-detail-kpi">
                                    <p class="inv-val-detail-kpi-label">Units</p>
                                    <p class="inv-val-detail-kpi-value">{{ number_format($detail['total_units'], 0) }}</p>
                                </div>
                                <div class="inv-val-detail-kpi">
                                    <p class="inv-val-detail-kpi-label">Value</p>
                                    <p class="inv-val-detail-kpi-value">{{ number_format($detail['total_value'], 2) }}</p>
                                </div>
                            </div>
                            <a href="{{ route('inventory.valuation') }}" class="mi-btn-ghost text-xs shrink-0">
                                <i class="fas fa-times text-[0.6rem]"></i> Close
                            </a>
                        </div>

                        <div class="mi-table-wrap">
                            <table class="mi-table text-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Avg Cost</th>
                                        <th>Line Value</th>
                                        <th>Share</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detail['balances'] as $balance)
                                        @php
                                            $lineValue = $balance->stockValue();
                                            $lineShare = $detail['total_value'] > 0
                                                ? round($lineValue / $detail['total_value'] * 100)
                                                : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="inv-product-cell">
                                                    <div class="inv-product-thumb !w-8 !h-8 !text-[0.7rem]"><i class="fas fa-box"></i></div>
                                                    <div class="inv-product-meta">
                                                        <p class="inv-product-part">{{ $balance->product->part_number }}</p>
                                                        <p class="inv-product-name">{{ Str::limit($balance->product->name, 42) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="font-medium">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                                            <td>{{ number_format($balance->average_cost, 2) }}</td>
                                            <td class="font-semibold text-orange-700">{{ number_format($lineValue, 2) }}</td>
                                            <td>
                                                <div class="flex items-center gap-2 min-w-[5rem]">
                                                    <div class="inv-val-bar-track flex-1">
                                                        <div class="inv-val-bar-fill" style="width: {{ max(4, $lineShare) }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-500">{{ $lineShare }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('inventory.show', $balance->product) }}" class="inv-ref-link text-xs" onclick="event.stopPropagation()">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="!py-10 text-center text-gray-400">No stock at this location.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($detail['balances']->isNotEmpty())
                                    <tfoot>
                                        <tr class="font-semibold bg-stone-50">
                                            <td colspan="3" class="text-right text-gray-600">Location total</td>
                                            <td class="text-orange-700">{{ number_format($detail['total_value'], 2) }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            @include('inventory.partials.valuation-guide', compact('detail', 'detailLocation'))
        </div>
    </div>
</x-app-layout>

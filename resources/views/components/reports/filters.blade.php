@props(['filters', 'reportType', 'definition' => [], 'shops', 'warehouses' => collect(), 'suppliers' => collect(), 'scopedShopId' => null, 'scopedWarehouseId' => null])

@php
    $filterConfig = $definition['filters'] ?? ['dates', 'preset'];
    $query = $filters->toQueryArray();
@endphp

<form method="GET" action="{{ route('reports.show', $reportType) }}" class="mi-card p-4">
    <div class="mi-form-grid items-end">
        <div>
            <label class="mi-field-label">Preset</label>
            <select name="preset" class="mi-select" onchange="this.form.querySelector('[name=date_from]').value=''; this.form.querySelector('[name=date_to]').value='';">
                <option value="">Custom range</option>
                <option value="today" @selected($filters->preset === 'today')>Today</option>
                <option value="7d" @selected($filters->preset === '7d')>Last 7 days</option>
                <option value="30d" @selected($filters->preset === '30d' || ! $filters->preset)>Last 30 days</option>
                <option value="mtd" @selected($filters->preset === 'mtd')>Month to date</option>
                <option value="ytd" @selected($filters->preset === 'ytd')>Year to date</option>
            </select>
        </div>
        <div>
            <label class="mi-field-label">From</label>
            <input type="date" name="date_from" class="mi-input block w-full" value="{{ $filters->from->format('Y-m-d') }}">
        </div>
        <div>
            <label class="mi-field-label">To</label>
            <input type="date" name="date_to" class="mi-input block w-full" value="{{ $filters->to->format('Y-m-d') }}">
        </div>
        @if (! $scopedShopId && in_array('shop', $filterConfig, true))
            <div>
                <label class="mi-field-label">Shop</label>
                <select name="shop_id" class="mi-select">
                    <option value="">All shops</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}" @selected($filters->shopId === $shop->id)>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if (! $scopedWarehouseId && in_array('warehouse', $filterConfig, true))
            <div>
                <label class="mi-field-label">Warehouse</label>
                <select name="warehouse_id" class="mi-select">
                    <option value="">All warehouses</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected($filters->warehouseId === $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if (in_array('supplier', $filterConfig, true))
            <div>
                <label class="mi-field-label">Supplier</label>
                <select name="supplier_id" class="mi-select">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected($filters->supplierId === $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="mi-btn-orange"><i class="fas fa-filter text-xs"></i> Apply</button>
            @can('reports.export')
                @if ($definition['export'] ?? true)
                    <a href="{{ route('reports.export', array_merge(['slug' => $reportType], $query)) }}"
                       class="mi-btn-ghost"><i class="fas fa-download text-xs"></i> CSV</a>
                @endif
            @endcan
        </div>
    </div>
</form>

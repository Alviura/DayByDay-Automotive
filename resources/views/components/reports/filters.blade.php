@props(['filters', 'reportType', 'shops', 'scopedShopId' => null])

<form method="GET" action="{{ route('reports.'.$reportType) }}" class="mi-card p-4">
    <div class="mi-form-grid items-end">
        <div>
            <label class="mi-field-label">From</label>
            <input type="date" name="date_from" class="mi-input block w-full" value="{{ $filters->from->format('Y-m-d') }}">
        </div>
        <div>
            <label class="mi-field-label">To</label>
            <input type="date" name="date_to" class="mi-input block w-full" value="{{ $filters->to->format('Y-m-d') }}">
        </div>
        @if (! $scopedShopId && in_array($reportType, ['sales', 'inventory', 'transfers', 'financial']))
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
        <div class="flex gap-2">
            <button type="submit" class="mi-btn-orange"><i class="fas fa-filter text-xs"></i> Apply</button>
            @can('reports.export')
                <a href="{{ route('reports.export', array_merge(['type' => $reportType], request()->only(['date_from', 'date_to', 'shop_id']))) }}"
                   class="mi-btn-ghost"><i class="fas fa-download text-xs"></i> CSV</a>
            @endcan
        </div>
    </div>
</form>

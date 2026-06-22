{{-- Product inventory sidebar --}}
<aside class="mi-card inv-sidebar overflow-hidden">
    <div class="inv-sidebar-hero">
        <div class="inv-sidebar-hero-icon"><i class="fas fa-box"></i></div>
        <div class="min-w-0">
            <p class="inv-sidebar-hero-label">Product</p>
            <p class="inv-sidebar-hero-title truncate">{{ $product->part_number }}</p>
            <p class="inv-sidebar-hero-sub truncate">{{ Str::limit($product->name, 36) }}</p>
        </div>
    </div>

    <div class="inv-sidebar-body">
        <div class="inv-sidebar-block">
            <p class="inv-section-title mb-3"><i class="fas fa-chart-pie"></i> Stock Totals</p>
            <dl class="inv-detail-list">
                <div class="inv-detail-row">
                    <dt>On hand</dt>
                    <dd class="font-semibold">{{ number_format($totals['on_hand'], 0) }}</dd>
                </div>
                <div class="inv-detail-row">
                    <dt>Available</dt>
                    <dd>{{ number_format($totals['available'], 0) }}</dd>
                </div>
                <div class="inv-detail-row">
                    <dt>Reserved</dt>
                    <dd>{{ number_format($totals['reserved'], 0) }}</dd>
                </div>
                <div class="inv-detail-row">
                    <dt>Stock value</dt>
                    <dd class="font-semibold text-orange-700">{{ number_format($totals['value'], 2) }} KES</dd>
                </div>
            </dl>
        </div>

        <div class="inv-sidebar-block">
            <p class="inv-section-title mb-3"><i class="fas fa-tags"></i> Cost Comparison</p>
            <dl class="inv-detail-list">
                <div class="inv-detail-row">
                    <dt>Master cost</dt>
                    <dd>{{ number_format($product->cost_price, 2) }}</dd>
                </div>
                <div class="inv-detail-row">
                    <dt>Ledger avg</dt>
                    <dd>{{ number_format($totals['ledger_avg'], 2) }}</dd>
                </div>
                <div class="inv-detail-row">
                    <dt>MKT wholesale</dt>
                    <dd>{{ number_format($product->min_selling_price, 2) }}</dd>
                </div>
                @if ($product->reorder_level > 0)
                    <div class="inv-detail-row">
                        <dt>Reorder at</dt>
                        <dd>{{ number_format($product->reorder_level, 0) }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($incoming['units'] > 0)
            <div class="inv-sidebar-block">
                <p class="inv-section-title mb-2"><i class="fas fa-truck"></i> On Order</p>
                <p class="text-sm font-semibold text-blue-800">{{ number_format($incoming['units'], 0) }} units incoming</p>
                <p class="text-xs text-gray-500 mt-1">{{ $incoming['lines'] }} open PO {{ str('line')->plural($incoming['lines']) }}</p>
            </div>
        @endif

        @can('transfers.request')
            <div class="inv-sidebar-block">
                <a href="{{ route('transfers.create', ['type' => 'warehouse_to_shop', 'product_id' => $product->id]) }}" class="inv-link-card">
                    <div class="flex items-center gap-2">
                        <div class="inv-link-card-icon bg-teal-50 text-teal-700 border border-teal-100"><i class="fas fa-right-left"></i></div>
                        <span class="text-sm font-semibold">Transfer to shop</span>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                </a>
            </div>
        @endcan

        <div class="inv-sidebar-block">
            <a href="{{ route('products.show', $product) }}" class="inv-link-card">
                <div class="flex items-center gap-2">
                    <div class="inv-link-card-icon bg-orange-50 text-orange-600 border border-orange-100"><i class="fas fa-cube"></i></div>
                    <span class="text-sm font-semibold">Product master</span>
                </div>
                <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
            </a>
        </div>
    </div>
</aside>

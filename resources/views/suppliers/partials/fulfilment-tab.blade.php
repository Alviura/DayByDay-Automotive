@props(['supplier', 'goodsReceipts', 'supplierReturns', 'stats'])

<div class="space-y-5">

    <div class="mi-kpi-row !grid-cols-3">
        <div class="mi-kpi mi-kpi-green">
            <div>
                <p class="mi-kpi-label">Goods Receipts</p>
                <p class="mi-kpi-value">{{ number_format($stats['grn_count']) }}</p>
                <p class="sp-kpi-sub">GRNs posted to warehouse</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-warehouse"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-purple">
            <div>
                <p class="mi-kpi-label">Received Value</p>
                <p class="mi-kpi-value">{{ number_format($stats['received_value'], 0) }}</p>
                <p class="sp-kpi-sub">KES into inventory</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
        </div>
        <div class="mi-kpi mi-kpi-amber">
            <div>
                <p class="mi-kpi-label">Returns</p>
                <p class="mi-kpi-value">{{ number_format($stats['return_count']) }}</p>
                <p class="sp-kpi-sub">Stock sent back</p>
            </div>
            <div class="mi-kpi-icon"><i class="fas fa-truck-ramp-box"></i></div>
        </div>
    </div>

    @can('procurement.view')
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center justify-between w-full gap-3">
                    <p class="text-sm font-semibold text-gray-800">Goods receipts</p>
                    <a href="{{ route('goods-receipts.index') }}" class="text-xs text-orange-600 font-semibold hover:underline">All GRNs</a>
                </div>
            </div>
            @if ($goodsReceipts->isNotEmpty())
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>GRN</th><th>PO</th><th>Warehouse</th><th>Lines</th><th>Status</th><th>Received</th></tr></thead>
                        <tbody>
                            @foreach ($goodsReceipts as $grn)
                                <tr class="sp-index-row" onclick="window.location='{{ route('goods-receipts.show', $grn) }}'">
                                    <td class="font-mono font-semibold">{{ $grn->grn_number }}</td>
                                    <td class="font-mono text-gray-600">{{ $grn->purchaseOrder?->po_number ?? '—' }}</td>
                                    <td>{{ $grn->warehouse?->name ?? '—' }}</td>
                                    <td>{{ $grn->items_count }}</td>
                                    <td>{{ $grn->statusLabel() }}</td>
                                    <td class="text-gray-500">{{ $grn->received_at?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mi-show-empty"><i class="fas fa-warehouse"></i><p>No goods receipts from this supplier yet.</p></div>
            @endif
        </div>
    @endcan

    @can('returns.view')
        <div class="mi-card">
            <div class="mi-card-head">
                <div class="flex items-center justify-between w-full gap-3">
                    <p class="text-sm font-semibold text-gray-800">Supplier returns</p>
                    @can('returns.create')
                        <a href="{{ route('supplier-returns.create') }}" class="text-xs text-orange-600 font-semibold hover:underline">New return</a>
                    @endcan
                </div>
            </div>
            @if ($supplierReturns->isNotEmpty())
                <div class="mi-table-wrap">
                    <table class="mi-table text-sm">
                        <thead><tr><th>Return</th><th>Warehouse</th><th>Lines</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($supplierReturns as $return)
                                <tr class="sp-index-row" onclick="window.location='{{ route('supplier-returns.show', $return) }}'">
                                    <td class="font-mono font-semibold">{{ $return->return_number }}</td>
                                    <td>{{ $return->warehouse?->name ?? '—' }}</td>
                                    <td>{{ $return->items_count }}</td>
                                    <td>@include('returns.partials.status-badge', ['return' => $return])</td>
                                    <td class="text-gray-500">{{ $return->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mi-show-empty"><i class="fas fa-rotate-left"></i><p>No returns to this supplier recorded.</p></div>
            @endif
        </div>
    @endcan
</div>

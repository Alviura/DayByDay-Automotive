<x-app-layout :title="$product->part_number.' — Inventory'">

    @push('styles')
        <x-module.page-index-styles />
        @include('inventory.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-box"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $product->name }}</h1>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
                        <span class="font-medium text-gray-700">{{ $product->part_number }}</span>
                        @if ($product->unit)
                            <span class="mi-cat-badge">{{ $product->unit->name }}</span>
                        @endif
                        @if ($product->category)
                            <span class="mi-cat-badge">{{ $product->category->name }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> All Stock</a>
                <a href="{{ route('inventory.movements', ['search' => $product->part_number]) }}" class="mi-btn-ghost"><i class="fas fa-right-left text-xs"></i> Movements</a>
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-green">
                <div>
                    <p class="mi-kpi-label">On Hand</p>
                    <p class="mi-kpi-value">{{ number_format($totals['on_hand'], 0) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-cubes"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-purple">
                <div>
                    <p class="mi-kpi-label">Available</p>
                    <p class="mi-kpi-value">{{ number_format($totals['available'], 0) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-check"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-amber">
                <div>
                    <p class="mi-kpi-label">On Order</p>
                    <p class="mi-kpi-value">{{ number_format($incoming['units'], 0) }}</p>
                    <p class="inv-kpi-sub">Open PO lines</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-truck"></i></div>
            </div>
            <div class="mi-kpi mi-kpi-orange">
                <div>
                    <p class="mi-kpi-label">Stock Value</p>
                    <p class="mi-kpi-value orange" style="font-size:1.1rem">{{ number_format($totals['value'], 2) }}</p>
                </div>
                <div class="mi-kpi-icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-5">
            <div class="space-y-5">
                <div class="mi-card">
                    <div class="mi-card-head">
                        <div>
                            <p class="inv-section-title"><i class="fas fa-warehouse"></i> Balances by Location</p>
                            <p class="inv-section-sub">Weighted average cost per warehouse or shop</p>
                        </div>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table text-sm">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>On Hand</th>
                                    <th>Reserved</th>
                                    <th>Available</th>
                                    <th>Avg Cost</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($balances as $balance)
                                    @php
                                        $isWh = $balance->location instanceof \App\Models\Warehouse;
                                        $transferParams = $isWh
                                            ? ['type' => 'warehouse_to_shop', 'source_id' => $balance->location_id, 'product_id' => $product->id]
                                            : ['type' => 'inter_shop', 'source_id' => $balance->location_id, 'product_id' => $product->id];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="inv-loc-chip {{ $isWh ? 'inv-loc-chip-wh' : 'inv-loc-chip-sh' }}">
                                                <i class="fas fa-{{ $isWh ? 'warehouse' : 'store' }} text-[0.6rem]"></i>
                                                {{ $balance->location?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="font-medium">{{ number_format($balance->quantity_on_hand, 0) }}</td>
                                        <td>{{ number_format($balance->quantity_reserved, 0) }}</td>
                                        <td>{{ number_format($balance->quantity_available, 0) }}</td>
                                        <td>{{ number_format($balance->average_cost, 2) }}</td>
                                        <td class="font-semibold">
                                            {{ number_format($balance->stockValue(), 2) }}
                                            @can('transfers.request')
                                                @if ($balance->quantity_available > 0)
                                                    <a href="{{ route('transfers.create', $transferParams) }}" class="block text-xs text-orange-600 font-semibold mt-0.5 hover:underline">Transfer →</a>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-gray-400 py-8">No stock at any location.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="qs-section-head w-full">
                            <div>
                                <p class="inv-section-title"><i class="fas fa-truck-ramp-box"></i> Procurement Receipts</p>
                                <p class="inv-section-sub">GRN lines that posted (or voided) stock for this product</p>
                            </div>
                        </div>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table text-sm">
                            <thead>
                                <tr>
                                    <th>GRN</th>
                                    <th>PO</th>
                                    <th>Warehouse</th>
                                    <th>Received</th>
                                    <th>Damaged</th>
                                    <th>Good Qty</th>
                                    <th>Unit Cost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentReceipts as $line)
                                    @php $grn = $line->goodsReceiptNote; @endphp
                                    <tr>
                                        <td>
                                            @if ($grn)
                                                <a href="{{ route('goods-receipts.show', $grn) }}" class="inv-ref-link">{{ $grn->grn_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-sm">
                                            @if ($grn?->purchaseOrder)
                                                <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}" class="text-orange-600 hover:underline">{{ $grn->purchaseOrder->po_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $grn?->warehouse?->name ?? '—' }}</td>
                                        <td>{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($line->received_quantity) }}</td>
                                        <td>
                                            @if ($line->damaged_quantity > 0)
                                                <span class="inv-qty-low">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($line->damaged_quantity) }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="inv-qty-in">{{ \App\Models\GoodsReceiptNoteItem::formatQuantity($line->goodQuantity()) }}</td>
                                        <td>{{ number_format($line->unit_cost, 2) }}</td>
                                        <td>
                                            @if ($grn?->isVoided())
                                                <span class="inv-badge inv-badge-rose">Voided</span>
                                            @else
                                                <span class="inv-badge inv-badge-green">Posted</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-gray-400 py-8">No goods receipts yet for this product.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mi-card">
                    <div class="mi-card-head">
                        <div class="flex items-center justify-between w-full gap-3">
                            <div>
                                <p class="inv-section-title"><i class="fas fa-right-left"></i> Recent Movements</p>
                                <p class="inv-section-sub">Latest ledger entries for this product</p>
                            </div>
                            <a href="{{ route('inventory.movements', ['search' => $product->part_number]) }}" class="text-xs text-orange-600 font-semibold">View all</a>
                        </div>
                    </div>
                    <div class="mi-table-wrap">
                        <table class="mi-table text-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Qty</th>
                                    <th>Unit Cost</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $movement)
                                    <tr>
                                        <td class="text-gray-500 whitespace-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                        <td><span class="{{ $movement->badgeClass() }}">{{ $movement->transactionLabel() }}</span></td>
                                        <td>{{ $movement->location?->name }}</td>
                                        <td class="{{ $movement->isInbound() ? 'inv-qty-in' : 'inv-qty-out' }}">
                                            {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 0) }}
                                        </td>
                                        <td>{{ $movement->unit_cost !== null ? number_format($movement->unit_cost, 2) : '—' }}</td>
                                        <td>@include('inventory.partials.movement-reference', ['movement' => $movement])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-gray-400 py-8">No movements recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('inventory.partials.show-sidebar', compact('product', 'totals', 'incoming'))
        </div>
    </div>
</x-app-layout>

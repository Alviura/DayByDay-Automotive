<x-app-layout :title="$folder->folder_number">
    @push('styles')<x-module.page-index-styles />@endpush

    <div class="mi-page space-y-5" x-data="{ tab: 'overview' }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[1.35rem] font-bold text-gray-900">{{ $folder->folder_number }}</h1>
                        <span class="mi-status-pending">{{ $folder->statusLabel() }}</span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $folder->supplier?->name }} · {{ $folder->currency }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('procurement.folders.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
                @if ($folder->canEdit())
                    @can('procurement.manage')
                        <a href="{{ route('procurement.folders.edit', $folder) }}" class="mi-btn-ghost"><i class="fas fa-pen text-xs"></i> Edit</a>
                    @endcan
                @endif
            </div>
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Line Cost</p><p class="mi-kpi-value">{{ number_format($folder->total_cost, 2) }}</p></div><div class="mi-kpi-icon"><i class="fas fa-receipt"></i></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Freight + Tax</p><p class="mi-kpi-value">{{ number_format($folder->total_freight + $folder->total_tax, 2) }}</p></div><div class="mi-kpi-icon"><i class="fas fa-ship"></i></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Landing Cost</p><p class="mi-kpi-value orange">{{ number_format($folder->total_landing_cost, 2) }}</p></div><div class="mi-kpi-icon"><i class="fas fa-coins"></i></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">Lines</p><p class="mi-kpi-value">{{ $folder->items->count() }}</p></div><div class="mi-kpi-icon"><i class="fas fa-list"></i></div></div>
        </div>

        <div class="mi-tab-bar">
            <button type="button" @click="tab = 'overview'" :class="{ 'active': tab === 'overview' }"><i class="fas fa-circle-info"></i> Overview</button>
            <button type="button" @click="tab = 'items'" :class="{ 'active': tab === 'items' }"><i class="fas fa-list"></i> Line Items</button>
            <button type="button" @click="tab = 'cost'" :class="{ 'active': tab === 'cost' }"><i class="fas fa-calculator"></i> Cost Analysis</button>
            <button type="button" @click="tab = 'workflow'" :class="{ 'active': tab === 'workflow' }"><i class="fas fa-diagram-project"></i> Workflow</button>
        </div>

        <div x-show="tab === 'overview'">
            <div class="mi-card p-6">
                <dl class="mi-detail-grid">
                    <div class="mi-detail-item"><dt class="mi-detail-label">Supplier</dt><dd>{{ $folder->supplier?->name }}</dd></div>
                    <div class="mi-detail-item"><dt class="mi-detail-label">Import Type</dt><dd>{{ $folder->import_type ?? '—' }}</dd></div>
                    <div class="mi-detail-item"><dt class="mi-detail-label">Exchange Rate</dt><dd>{{ $folder->exchange_rate }}</dd></div>
                    <div class="mi-detail-item mi-span-full"><dt class="mi-detail-label">Notes</dt><dd>{{ $folder->notes ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>

        <div x-show="tab === 'items'" x-cloak class="space-y-4">
            @if ($folder->canEdit())
                @can('procurement.manage')
                    <div class="mi-card p-5">
                        <form method="POST" action="{{ route('procurement.folders.items.store', $folder) }}" class="mi-form-grid">
                            @csrf
                            <div>
                                <label class="mi-field-label">Product</label>
                                <select name="product_id" class="mi-select" required>
                                    <option value="">Select…</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->part_number }} — {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="mi-field-label">Qty</label><x-text-input name="quantity" type="number" step="0.01" min="0.01" class="mi-input block w-full" required /></div>
                            <div><label class="mi-field-label">Unit Cost</label><x-text-input name="unit_cost" type="number" step="0.01" min="0" class="mi-input block w-full" required /></div>
                            <div><label class="mi-field-label">CBM</label><x-text-input name="cbm" type="number" step="0.0001" min="0" class="mi-input block w-full" /></div>
                            <div class="flex items-end"><button type="submit" class="mi-btn-orange">Add Line</button></div>
                        </form>
                    </div>
                @endcan
            @endif
            <div class="mi-card">
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Unit Cost</th><th>CBM</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($folder->items as $item)
                                <tr>
                                    <td>{{ $item->product->part_number }}</td>
                                    <td>{{ number_format($item->quantity, 2) }}</td>
                                    <td>{{ number_format($item->unit_cost, 2) }}</td>
                                    <td>{{ $item->cbm ? number_format($item->cbm, 4) : '—' }}</td>
                                    <td>
                                        @if ($folder->canEdit())
                                            @can('procurement.manage')
                                                <form action="{{ route('procurement.folders.items.destroy', [$folder, $item]) }}" method="POST" class="inline" onsubmit="return confirm('Remove line?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="mi-action del"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-gray-400 py-8">No line items yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="tab === 'cost'" x-cloak class="space-y-4">
            @can('procurement.manage')
                @if ($folder->canEdit())
                    <div class="mi-card p-5">
                        <form method="POST" action="{{ route('procurement.folders.cost-analysis', $folder) }}" class="mi-form-grid">
                            @csrf
                            <div><label class="mi-field-label">Total Freight</label><x-text-input name="total_freight" type="number" step="0.01" class="mi-input block w-full" :value="$folder->total_freight" /></div>
                            <div><label class="mi-field-label">Total Tax</label><x-text-input name="total_tax" type="number" step="0.01" class="mi-input block w-full" :value="$folder->total_tax" /></div>
                            <div><label class="mi-field-label">Default Margin %</label><x-text-input name="default_margin" type="number" step="0.1" class="mi-input block w-full" value="30" /></div>
                            <div class="flex items-end"><button type="submit" class="mi-btn-orange"><i class="fas fa-calculator text-xs"></i> Run Analysis</button></div>
                        </form>
                    </div>
                @endif
            @endcan
            <div class="mi-card">
                <div class="mi-table-wrap">
                    <table class="mi-table">
                        <thead>
                            <tr>
                                <th>Product</th><th>Line Cost</th><th>Freight</th><th>Tax</th>
                                <th>Landing</th><th>CPU</th><th>Rec. Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($folder->items as $item)
                                <tr>
                                    <td>{{ $item->product->part_number }}</td>
                                    <td>{{ number_format($item->total_cost, 2) }}</td>
                                    <td>{{ number_format($item->freight_charge, 2) }}</td>
                                    <td>{{ number_format($item->tax_cost, 2) }}</td>
                                    <td>{{ number_format($item->landing_cost, 2) }}</td>
                                    <td>{{ number_format($item->cost_per_unit, 2) }}</td>
                                    <td class="font-medium text-orange-600">{{ number_format($item->recommended_selling_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="tab === 'workflow'" x-cloak class="space-y-4">
            @can('procurement.manage')
                <div class="mi-card p-5 flex flex-wrap gap-2">
                    @if ($folder->canSubmit())
                        <form action="{{ route('procurement.folders.submit', $folder) }}" method="POST" class="inline" onsubmit="return confirm('Submit for approval?');">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit for Approval</button>
                        </form>
                    @endif
                    @if ($folder->canGeneratePo())
                        <form action="{{ route('procurement.folders.generate-po', $folder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-file-invoice text-xs"></i> Generate PO</button>
                        </form>
                    @endif
                    @if (in_array($folder->status, ['po_generated', 'approved']))
                        <form action="{{ route('procurement.folders.in-transit', $folder) }}" method="POST" class="inline">@csrf<button type="submit" class="mi-btn-ghost">Mark In Transit</button></form>
                    @endif
                    @if (in_array($folder->status, ['received', 'in_transit', 'po_generated']))
                        <form action="{{ route('procurement.folders.close', $folder) }}" method="POST" class="inline" onsubmit="return confirm('Close this folder?');">@csrf<button type="submit" class="mi-btn-ghost">Close Folder</button></form>
                    @endif
                </div>
            @endcan

            @if ($folder->approval)
                <div class="mi-card p-5">
                    <a href="{{ route('approvals.show', $folder->approval) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                        View approval #{{ $folder->approval->id }} →
                    </a>
                </div>
            @endif

            @if ($folder->purchaseOrders->isNotEmpty())
                <div class="mi-card">
                    <div class="mi-card-head"><span class="text-sm font-semibold">Purchase Orders</span></div>
                    <div class="p-4 space-y-2">
                        @foreach ($folder->purchaseOrders as $po)
                            <a href="{{ route('purchase-orders.show', $po) }}" class="mi-cat-badge">{{ $po->po_number }} — {{ $po->statusLabel() }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($folder->goodsReceiptNotes->isNotEmpty())
                <div class="mi-card">
                    <div class="mi-card-head"><span class="text-sm font-semibold">Goods Receipts</span></div>
                    <div class="p-4 space-y-2">
                        @foreach ($folder->goodsReceiptNotes as $grn)
                            <a href="{{ route('goods-receipts.show', $grn) }}" class="mi-cat-badge">{{ $grn->grn_number }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<x-app-layout title="Stock Movements">

    @push('styles')
        <x-module.page-index-styles />
        @include('inventory.partials.page-styles')
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Stock Movements</h1>
                    <p class="text-sm text-gray-500">Append-only ledger — every stock-in, stock-out, and receipt reversal.</p>
                </div>
            </div>
            <a href="{{ route('inventory.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Balances</a>
        </div>

        @if ($typeSummary->isNotEmpty())
            <div class="mi-kpi-row">
                @foreach ($typeSummary->take(4) as $row)
                    <div class="mi-kpi mi-kpi-purple">
                        <div>
                            <p class="mi-kpi-label">{{ \App\Models\StockLedger::TYPE_LABELS[$row->transaction_type] ?? ucwords($row->transaction_type) }}</p>
                            <p class="mi-kpi-value" style="font-size:1.1rem">{{ number_format($row->entries) }}</p>
                            <p class="inv-kpi-sub">Net qty {{ number_format($row->net_qty, 0) }}</p>
                        </div>
                        <div class="mi-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mi-card">
            <div class="mi-card-head">
                <p class="inv-section-title"><i class="fas fa-filter"></i> Filter Movements</p>
            </div>
            <form method="GET" class="p-4 border-t border-gray-100">
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Product, GRN, PO, notes…">
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Type</label>
                        <select name="transaction_type" class="mi-select">
                            <option value="">All types</option>
                            @foreach ($transactionTypes as $type)
                                <option value="{{ $type }}" @selected(request('transaction_type') === $type)>
                                    {{ \App\Models\StockLedger::TYPE_LABELS[$type] ?? ucwords($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">From date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mi-input">
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">To date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mi-input">
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Location type</label>
                        <select name="location_type" class="mi-select">
                            <option value="">All types</option>
                            <option value="warehouse" @selected(request('location_type') === 'warehouse')>Warehouse</option>
                            <option value="shop" @selected(request('location_type') === 'shop')>Shop</option>
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Location</label>
                        <select name="location_id" class="mi-select">
                            <option value="">All locations</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" @selected(request('location_id') == $w->id && request('location_type') === 'warehouse')>{{ $w->name }} (WH)</option>
                            @endforeach
                            @foreach ($shops as $s)
                                <option value="{{ $s->id }}" @selected(request('location_id') == $s->id && request('location_type') === 'shop')>{{ $s->name }} (Shop)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <label class="mi-field-label">Sort</label>
                        <select name="sort" class="mi-select">
                            <option value="newest" @selected(request('sort', 'newest') !== 'oldest')>Newest first</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                        </select>
                    </div>
                </div>
                <div class="mi-filter-actions mt-4">
                    <button type="submit" class="mi-btn-orange"><i class="fas fa-magnifying-glass text-xs"></i> Apply</button>
                    <a href="{{ route('inventory.movements') }}" class="mi-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table text-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Line Value</th>
                            <th>Balance</th>
                            <th>Reference</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="text-gray-500 whitespace-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('inventory.show', $movement->product) }}" class="text-sm font-semibold text-gray-900 hover:text-orange-600">
                                        {{ $movement->product->part_number }}
                                    </a>
                                    <p class="text-xs text-gray-400 truncate max-w-[8rem]">{{ Str::limit($movement->product->name, 28) }}</p>
                                </td>
                                <td>{{ $movement->location?->name }}</td>
                                <td><span class="{{ $movement->badgeClass() }}">{{ $movement->transactionLabel() }}</span></td>
                                <td class="{{ $movement->isInbound() ? 'inv-qty-in' : 'inv-qty-out' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 0) }}
                                </td>
                                <td>{{ $movement->unit_cost !== null ? number_format($movement->unit_cost, 2) : '—' }}</td>
                                <td>{{ number_format($movement->lineValue(), 2) }}</td>
                                <td>{{ number_format($movement->balance_after, 0) }}</td>
                                <td>@include('inventory.partials.movement-reference', ['movement' => $movement])</td>
                                <td class="text-gray-500">{{ $movement->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="!py-14 text-center">
                                    <div class="inv-empty-icon"><i class="fas fa-right-left"></i></div>
                                    <p class="text-gray-500">No movements match your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())
                <div class="mi-card-foot">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

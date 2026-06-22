<div class="mi-card">
    <div class="mi-card-head">
        <div class="flex items-center justify-between w-full gap-3">
            <div>
                <p class="inv-section-title"><i class="fas fa-right-left"></i> Stock Movement History</p>
                <p class="inv-section-sub">Latest ledger entries for this product</p>
            </div>
            @can('inventory.view')
                <a href="{{ route('inventory.movements', ['search' => $product->part_number]) }}" class="text-xs text-orange-600 font-semibold hover:underline">
                    View all movements
                </a>
            @endcan
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
                    <th>Balance After</th>
                    <th>Unit Cost</th>
                    <th>Reference</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td class="text-gray-500 whitespace-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                        <td><span class="{{ $movement->badgeClass() }}">{{ $movement->transactionLabel() }}</span></td>
                        <td>
                            @php $isWh = $movement->location instanceof \App\Models\Warehouse; @endphp
                            <span class="inv-loc-chip {{ $isWh ? 'inv-loc-chip-wh' : 'inv-loc-chip-sh' }}">
                                <i class="fas fa-{{ $isWh ? 'warehouse' : 'store' }} text-[0.55rem]"></i>
                                {{ $movement->location?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="{{ $movement->isInbound() ? 'inv-qty-in' : 'inv-qty-out' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 0) }}
                        </td>
                        <td class="font-medium">{{ number_format($movement->balance_after, 0) }}</td>
                        <td>{{ $movement->unit_cost !== null ? number_format($movement->unit_cost, 2) : '—' }}</td>
                        <td>@include('inventory.partials.movement-reference', ['movement' => $movement])</td>
                        <td class="text-gray-500 text-xs">{{ $movement->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="!py-10 text-center text-gray-400">
                            No stock movements recorded for this product yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($movements->isNotEmpty())
        <div class="inv-index-hint">
            <i class="fas fa-circle-info"></i>
            <span>Showing the {{ $movements->count() }} most recent entries. Use the inventory movements page for full history and filters.</span>
        </div>
    @endif
</div>

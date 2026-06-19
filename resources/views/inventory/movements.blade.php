<x-app-layout title="Stock Movements">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5" x-data="{ filtersOpen: true }">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Stock Movements</h1>
                    <p class="text-sm text-gray-500">Append-only ledger — every stock-in and stock-out event.</p>
                </div>
            </div>
            <a href="{{ route('inventory.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Balances</a>
        </div>

        <div class="mi-card">
            <form method="GET" class="p-4">
                <div class="mi-filter-grid">
                    <div class="mi-filter-field">
                        <input type="text" name="search" value="{{ request('search') }}" class="mi-input" placeholder="Product, reference…">
                    </div>
                    <div class="mi-filter-field">
                        <select name="transaction_type" class="mi-select">
                            <option value="">All types</option>
                            @foreach ($transactionTypes as $type)
                                <option value="{{ $type }}" @selected(request('transaction_type') === $type)>{{ str_replace('_', ' ', ucwords($type, '_')) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mi-filter-field">
                        <button type="submit" class="mi-btn-orange w-full justify-center">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Balance</th>
                            <th>Reference</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="text-sm text-gray-500 whitespace-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('inventory.show', $movement->product) }}" class="text-sm font-medium text-orange-600">
                                        {{ $movement->product->part_number }}
                                    </a>
                                </td>
                                <td class="text-sm">{{ $movement->location?->name }}</td>
                                <td><span class="mi-cat-badge">{{ $movement->transactionLabel() }}</span></td>
                                <td class="font-medium {{ $movement->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 2) }}
                                </td>
                                <td>{{ number_format($movement->balance_after, 2) }}</td>
                                <td class="text-sm text-gray-500">{{ $movement->reference_number ?? '—' }}</td>
                                <td class="text-sm text-gray-500">{{ $movement->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="!py-14 text-center text-gray-400">No movements found.</td></tr>
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

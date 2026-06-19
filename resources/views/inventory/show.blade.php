<x-app-layout :title="$product->part_number">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-box"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $product->part_number }}</p>
                </div>
            </div>
            <a href="{{ route('inventory.index') }}" class="mi-btn-ghost"><i class="fas fa-arrow-left text-xs"></i> Back</a>
        </div>

        <div class="mi-card">
            <div class="mi-card-head"><span class="text-sm font-semibold">Balances by Location</span></div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Location</th><th>On Hand</th><th>Reserved</th><th>Available</th><th>Avg Cost</th></tr></thead>
                    <tbody>
                        @forelse ($balances as $balance)
                            <tr>
                                <td>{{ $balance->location?->name ?? '—' }}</td>
                                <td>{{ number_format($balance->quantity_on_hand, 2) }}</td>
                                <td>{{ number_format($balance->quantity_reserved, 2) }}</td>
                                <td>{{ number_format($balance->quantity_available, 2) }}</td>
                                <td>{{ number_format($balance->average_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray-400 py-8">No stock at any location.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mi-card">
            <div class="mi-card-head">
                <span class="text-sm font-semibold">Recent Movements</span>
                <a href="{{ route('inventory.movements', ['search' => $product->part_number]) }}" class="text-sm text-orange-600">View all</a>
            </div>
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Date</th><th>Type</th><th>Location</th><th>Qty</th><th>Balance After</th></tr></thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="text-sm text-gray-500">{{ $movement->created_at->format('d M Y H:i') }}</td>
                                <td><span class="mi-cat-badge">{{ $movement->transactionLabel() }}</span></td>
                                <td>{{ $movement->location?->name }}</td>
                                <td class="{{ $movement->quantity < 0 ? 'text-red-600' : 'text-green-600' }} font-medium">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 2) }}
                                </td>
                                <td>{{ number_format($movement->balance_after, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray-400 py-8">No movements yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

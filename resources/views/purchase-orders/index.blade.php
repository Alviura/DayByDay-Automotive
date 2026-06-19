<x-app-layout title="Purchase Orders">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex items-start gap-3">
            <div class="mi-page-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold text-gray-900">Purchase Orders</h1>
                <p class="text-sm text-gray-500">POs generated from approved procurement folders.</p>
            </div>
        </div>
        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>PO #</th><th>Folder</th><th>Supplier</th><th>Total</th><th>Status</th><th>Delivery</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td><a href="{{ route('purchase-orders.show', $order) }}" class="mi-cat-badge">{{ $order->po_number }}</a></td>
                                <td>{{ $order->folder?->folder_number ?? '—' }}</td>
                                <td>{{ $order->supplier?->name }}</td>
                                <td>{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                                <td>{{ $order->statusLabel() }}</td>
                                <td>{{ $order->deliveryLabel() }}</td>
                                <td><a href="{{ route('purchase-orders.show', $order) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-12 text-gray-400">No purchase orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())<div class="mi-card-foot">{{ $orders->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>

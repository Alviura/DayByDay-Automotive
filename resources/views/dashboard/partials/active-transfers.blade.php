@props(['transfers' => collect()])

<div class="mi-card">
    <div class="mi-card-head">
        <h2 class="text-sm font-bold text-gray-900">Active Transfers</h2>
        <a href="{{ route('stock-transfers.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">View all</a>
    </div>
    @if ($transfers->isEmpty())
        <div class="db-empty"><i class="fas fa-truck mb-2 block text-lg opacity-40"></i>No active stock transfers.</div>
    @else
        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Transfer</th>
                        <th>Route</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transfers as $transfer)
                        <tr>
                            <td>
                                <a href="{{ route('stock-transfers.show', $transfer) }}">{{ $transfer->transfer_number }}</a>
                            </td>
                            <td title="{{ $transfer->routeLabel() }}">{{ Str::limit($transfer->routeLabel(), 22) }}</td>
                            <td><span class="text-xs font-semibold text-gray-500">{{ $transfer->statusLabel() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

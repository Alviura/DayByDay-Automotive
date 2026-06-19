<x-app-layout :title="$return->return_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[1.35rem] font-bold">{{ $return->return_number }}</h1>
                    <span class="mi-status-pending">{{ $return->statusLabel() }}</span>
                </div>
                <p class="text-sm text-gray-500">Sale {{ $return->sale?->receipt_number }} · {{ $return->shop?->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customer-returns.index') }}" class="mi-btn-ghost">Back</a>
                @if ($return->canSubmit())
                    @can('returns.create')
                        <form action="{{ route('customer-returns.submit', $return) }}" method="POST" class="inline" onsubmit="return confirm('Submit for approval?');">
                            @csrf
                            <button type="submit" class="mi-btn-orange"><i class="fas fa-paper-plane text-xs"></i> Submit</button>
                        </form>
                    @endcan
                @endif
                @if ($return->approval)
                    <a href="{{ route('approvals.show', $return->approval) }}" class="mi-btn-ghost">View Approval</a>
                @endif
            </div>
        </div>

        <div class="mi-card p-5">
            <p class="text-sm"><strong>Reason:</strong> {{ $return->reason }}</p>
            @if ($return->status === 'completed')
                <p class="text-sm mt-2"><strong>Refund:</strong> {{ number_format($return->refund_amount, 2) }}</p>
            @endif
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Condition</th><th>Restock</th><th>Refund</th></tr></thead>
                    <tbody>
                        @foreach ($return->items as $item)
                            <tr>
                                <td>{{ $item->product->part_number }} — {{ $item->product->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->conditionLabel() }}</td>
                                <td>{{ $item->restock ? 'Yes' : 'No' }}</td>
                                <td>{{ number_format($item->lineRefund(), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout :title="$return->return_number">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[1.35rem] font-bold">{{ $return->return_number }}</h1>
                    <span class="mi-status-pending">{{ $return->statusLabel() }}</span>
                </div>
                <p class="text-sm text-gray-500">{{ $return->supplier?->name }} · {{ $return->warehouse?->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('supplier-returns.index') }}" class="mi-btn-ghost">Back</a>
                @if ($return->canSubmit())
                    @can('returns.create')
                        <form action="{{ route('supplier-returns.submit', $return) }}" method="POST" class="inline" onsubmit="return confirm('Submit for approval?');">
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

        <div class="mi-card p-5"><p class="text-sm"><strong>Reason:</strong> {{ $return->reason }}</p></div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Condition</th></tr></thead>
                    <tbody>
                        @foreach ($return->items as $item)
                            <tr>
                                <td>{{ $item->product->part_number }} — {{ $item->product->name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->conditionLabel() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<div class="mi-table-wrap overflow-x-auto qs-summary-scroll">
    <table class="mi-table text-sm">
        <thead>
            <tr>
                <th>Part Number</th>
                <th>Quantity</th>
                @if ($series->isImport())
                    <th>Unit Price ({{ $series->currency }})</th>
                    <th>Total Purchase ({{ $series->currency }})</th>
                    <th>Unit Price (KES)</th>
                    <th>Quantity per Packet</th>
                    <th>Number of Packets</th>
                    <th>Total CBM</th>
                    <th>Transport per Unit</th>
                @else
                    <th>Unit Price (KES)</th>
                    <th>Total Purchase (KES)</th>
                    <th>Line Transport (KES)</th>
                @endif
                <th>Unit Cost (Arrival)</th>
                <th>MKT Wholesale Price</th>
                <th>Margin</th>
                <th>Margin %</th>
                <th>Actual Total Cost</th>
                <th>Expected Sales</th>
                <th>Expected Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($series->items as $item)
                @php
                    $marginPositive = $item->margin_amount !== null && $item->margin_amount >= 0;
                    $summaryQtyPerPacket = (float) ($item->quantity_per_packet ?: 1);
                    $summaryPackets = $item->number_of_packets !== null
                        ? (float) $item->number_of_packets
                        : \App\Services\Procurement\ImportOrderCalculator::deriveNumberOfPackets((float) $item->quantity, $summaryQtyPerPacket);
                @endphp
                <tr>
                    <td class="font-medium whitespace-nowrap">{{ $item->product->part_number }}</td>
                    <td>{{ number_format($item->quantity, 0) }}</td>
                    @if ($series->isImport())
                        <td>{{ $item->unit_price_foreign ? number_format($item->unit_price_foreign, 4) : '—' }}</td>
                        <td>{{ $item->total_purchase_price ? number_format($item->total_purchase_price, 2) : '—' }}</td>
                        <td>{{ $item->unit_price_ksh ? number_format($item->unit_price_ksh, 2) : '—' }}</td>
                        <td>{{ number_format($summaryQtyPerPacket, 2) }}</td>
                        <td>{{ number_format($summaryPackets, 2) }}</td>
                        <td>{{ $item->total_cbm ? number_format($item->total_cbm, 2) : '—' }}</td>
                        <td>{{ $item->transport_per_unit ? number_format($item->transport_per_unit, 2) : '—' }}</td>
                    @else
                        <td>{{ $item->unit_price ? number_format($item->unit_price, 2) : '—' }}</td>
                        <td>{{ $item->total_purchase_price ? number_format($item->total_purchase_price, 2) : '—' }}</td>
                        <td>{{ number_format($item->transport ?? 0, 2) }}</td>
                    @endif
                    <td>{{ $item->unit_cost_arrival ? number_format($item->unit_cost_arrival, 2) : '—' }}</td>
                    <td>{{ number_format($item->resolveMarketWholesalePrice(), 2) }}</td>
                    <td class="{{ $marginPositive ? 'text-green-700' : 'text-red-600' }} font-medium">{{ $item->margin_amount !== null ? number_format($item->margin_amount, 2) : '—' }}</td>
                    <td class="{{ $marginPositive ? 'text-green-700' : 'text-red-600' }}">{{ $item->margin_percent !== null ? number_format($item->margin_percent, 1).'%' : '—' }}</td>
                    <td>{{ $item->actual_total_cost ? number_format($item->actual_total_cost, 2) : '—' }}</td>
                    <td>{{ $item->expected_sales ? number_format($item->expected_sales, 2) : '—' }}</td>
                    <td class="font-medium text-green-700">{{ $item->expected_margin ? number_format($item->expected_margin, 2) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        @if ($series->isCalculated())
            <tfoot class="font-semibold bg-stone-50 text-sm">
                @if ($series->isImport())
                    <tr><td colspan="13" class="text-right text-gray-500">Total purchase ({{ $series->currency }})</td><td colspan="3">{{ number_format($series->total_purchase_price, 2) }}</td></tr>
                    <tr><td colspan="13" class="text-right text-gray-500">Total CBM</td><td colspan="3">{{ number_format($series->total_cbm, 2) }}</td></tr>
                @endif
                <tr><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-500">Total transport</td><td colspan="3">{{ number_format($series->total_transport_cost, 2) }}</td></tr>
                <tr><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-500">Total actual cost</td><td colspan="3" class="text-orange-700">{{ number_format($series->total_actual_cost, 2) }}</td></tr>
                <tr><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-500">Total expected sales</td><td colspan="3">{{ number_format($series->total_expected_sales, 2) }}</td></tr>
                <tr class="bg-green-50"><td colspan="{{ $series->isImport() ? 13 : 9 }}" class="text-right text-gray-600">Total expected margin</td><td colspan="3" class="text-green-700">{{ number_format($series->total_expected_margin, 2) }}</td></tr>
            </tfoot>
        @endif
    </table>
</div>

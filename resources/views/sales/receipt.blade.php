<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sale->receipt_number }} — Receipt</title>
    <style>
        body { font-family: 'Courier New', monospace; max-width: 320px; margin: 2rem auto; padding: 1rem; color: #111; }
        .center { text-align: center; }
        .divider { border-top: 1px dashed #999; margin: .75rem 0; }
        table { width: 100%; font-size: 12px; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="center">
        <p class="bold">{{ $sale->shop?->name }}</p>
        <p style="font-size:11px">{{ $sale->shop?->address }}</p>
        <p style="font-size:11px">{{ $sale->shop?->phone }}</p>
    </div>

    <div class="divider"></div>

    <p style="font-size:12px">
        Receipt: <strong>{{ $sale->receipt_number }}</strong><br>
        Date: {{ $sale->sold_at?->format('d M Y H:i') }}<br>
        Cashier: {{ $sale->cashier?->name }}<br>
        @if ($sale->customer_name)Customer: {{ $sale->customer_name }}<br>@endif
    </p>

    <div class="divider"></div>

    <table>
        @foreach ($sale->items as $item)
            <tr>
                <td colspan="2">{{ $item->product->part_number }}</td>
            </tr>
            <tr>
                <td>{{ number_format($item->quantity, 0) }} x {{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr><td>Subtotal</td><td class="right">{{ number_format($sale->subtotal, 2) }}</td></tr>
        @if ($sale->discount_total > 0)
            <tr><td>Discount</td><td class="right">-{{ number_format($sale->discount_total, 2) }}</td></tr>
        @endif
        @if ($sale->tax_total > 0)
            <tr><td>Tax</td><td class="right">{{ number_format($sale->tax_total, 2) }}</td></tr>
        @endif
        <tr class="bold"><td>TOTAL</td><td class="right">{{ number_format($sale->total, 2) }}</td></tr>
        <tr><td>Paid</td><td class="right">{{ number_format($sale->amount_paid, 2) }}</td></tr>
        @if ($sale->change_due > 0)
            <tr><td>Change</td><td class="right">{{ number_format($sale->change_due, 2) }}</td></tr>
        @endif
    </table>

    <div class="divider"></div>

    @foreach ($sale->payments as $payment)
        <p style="font-size:11px">{{ $payment->methodLabel() }}: {{ number_format($payment->amount, 2) }}</p>
    @endforeach

    <div class="divider"></div>
    <p class="center" style="font-size:11px">Thank you for your business!</p>

    <p class="center no-print" style="margin-top:2rem">
        <button onclick="window.print()" style="padding:.5rem 1rem;cursor:pointer">Print</button>
        <a href="{{ route('sales.pos') }}" style="margin-left:.5rem">Back to POS</a>
    </p>
</body>
</html>

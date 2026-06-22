<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sale->receipt_number }} — Receipt</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Figtree', system-ui, -apple-system, sans-serif;
            background: #f3f4f6;
            color: #111827;
            min-height: 100vh;
            padding: 2rem 1rem 3rem;
            -webkit-font-smoothing: antialiased;
        }

        .rcp-page {
            max-width: 420px;
            margin: 0 auto;
        }

        .rcp-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .rcp-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.15rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .15s;
        }
        .rcp-btn-primary {
            background: #ff6b35;
            color: #fff;
            box-shadow: 0 2px 8px rgba(255, 107, 53, .35);
        }
        .rcp-btn-primary:hover { background: #e85a28; }
        .rcp-btn-ghost {
            background: #fff;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }
        .rcp-btn-ghost:hover { background: #f9fafb; border-color: #d1d5db; }

        .rcp-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            border: 1px solid #f0f0f0;
        }

        .rcp-accent-bar {
            height: 5px;
            background: linear-gradient(90deg, #ff6b35, #f59e0b);
        }

        .rcp-header {
            text-align: center;
            padding: 1.75rem 1.5rem 1.25rem;
            background: linear-gradient(180deg, #fffbf5 0%, #fff 100%);
            border-bottom: 1px solid #f3f4f6;
        }

        .rcp-logo {
            width: 3rem;
            height: 3rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #ff6b35, #ea580c);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin: 0 auto .85rem;
            box-shadow: 0 4px 12px rgba(255, 107, 53, .3);
        }

        .rcp-shop-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -.01em;
        }

        .rcp-shop-meta {
            margin-top: .35rem;
            font-size: .78rem;
            color: #6b7280;
            line-height: 1.5;
        }

        .rcp-receipt-no {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-top: 1rem;
            padding: .35rem .85rem;
            border-radius: 9999px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: .78rem;
            font-weight: 700;
            color: #c2410c;
        }

        .rcp-body { padding: 1.25rem 1.5rem; }

        .rcp-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem .85rem;
            padding: .85rem 1rem;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid #f3f4f6;
            margin-bottom: 1.25rem;
        }

        .rcp-meta-item { min-width: 0; }
        .rcp-meta-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
        }
        .rcp-meta-value {
            font-size: .78rem;
            font-weight: 600;
            color: #374151;
            margin-top: .15rem;
            word-break: break-word;
        }
        .rcp-meta-full { grid-column: 1 / -1; }

        .rcp-section-title {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: .65rem;
        }

        .rcp-items { margin-bottom: 1.25rem; }

        .rcp-line {
            padding: .65rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .rcp-line:last-child { border-bottom: none; }

        .rcp-line-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
        }

        .rcp-line-name {
            font-size: .82rem;
            font-weight: 600;
            color: #111827;
            line-height: 1.35;
        }

        .rcp-line-sku {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: .68rem;
            color: #9ca3af;
            margin-top: .15rem;
        }

        .rcp-line-total {
            font-size: .85rem;
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
        }

        .rcp-line-detail {
            display: flex;
            justify-content: space-between;
            margin-top: .25rem;
            font-size: .72rem;
            color: #6b7280;
        }

        .rcp-totals {
            border-top: 2px dashed #e5e7eb;
            padding-top: 1rem;
            margin-bottom: 1.25rem;
        }

        .rcp-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .25rem 0;
            font-size: .82rem;
            color: #6b7280;
        }
        .rcp-total-row span:last-child {
            font-weight: 600;
            color: #374151;
        }
        .rcp-total-row.discount span:last-child { color: #059669; }

        .rcp-grand-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: .65rem;
            padding: .85rem 1rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #1f2937, #111827);
            color: #fff;
        }
        .rcp-grand-total-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
        }
        .rcp-grand-total-amount {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fdba74;
        }

        .rcp-paid-row {
            display: flex;
            justify-content: space-between;
            margin-top: .65rem;
            font-size: .82rem;
            color: #6b7280;
        }
        .rcp-paid-row strong { color: #374151; }
        .rcp-change {
            color: #059669;
            font-weight: 700;
        }

        .rcp-payments { margin-bottom: 1.25rem; }

        .rcp-payment-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .55rem .75rem;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            margin-bottom: .4rem;
            font-size: .78rem;
        }
        .rcp-payment-pill:last-child { margin-bottom: 0; }

        .rcp-payment-method {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-weight: 600;
            color: #374151;
        }
        .rcp-payment-icon {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
        }
        .rcp-pay-cash   { background: #dcfce7; color: #15803d; }
        .rcp-pay-mpesa  { background: #dbeafe; color: #1d4ed8; }
        .rcp-pay-card   { background: #f3e8ff; color: #7c3aed; }
        .rcp-pay-bank   { background: #fef3c7; color: #b45309; }
        .rcp-pay-other  { background: #f1f5f9; color: #475569; }

        .rcp-payment-amount { font-weight: 700; color: #111827; }
        .rcp-payment-ref {
            font-size: .65rem;
            color: #9ca3af;
            margin-top: .1rem;
        }

        .rcp-footer {
            text-align: center;
            padding: 1.25rem 1.5rem 1.5rem;
            border-top: 1px solid #f3f4f6;
            background: #fafafa;
        }

        .rcp-thanks {
            font-size: .875rem;
            font-weight: 700;
            color: #374151;
        }
        .rcp-footer-sub {
            font-size: .72rem;
            color: #9ca3af;
            margin-top: .35rem;
        }
        .rcp-brand {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-top: .85rem;
            font-size: .65rem;
            font-weight: 600;
            color: #d1d5db;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .rcp-brand i { color: #ff6b35; }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print { display: none !important; }
            .rcp-page { max-width: 100%; }
            .rcp-card {
                box-shadow: none;
                border: none;
                border-radius: 0;
            }
            .rcp-accent-bar { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .rcp-grand-total { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .rcp-logo { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $cashier = $sale->completedBy ?? $sale->cashier;
        $paymentIcon = fn (string $method) => match ($method) {
            'cash' => ['fa-money-bill-wave', 'rcp-pay-cash'],
            'mpesa' => ['fa-mobile-screen', 'rcp-pay-mpesa'],
            'card' => ['fa-credit-card', 'rcp-pay-card'],
            'bank_transfer' => ['fa-building-columns', 'rcp-pay-bank'],
            default => ['fa-wallet', 'rcp-pay-other'],
        };
    @endphp

    <div class="rcp-page">
        <div class="rcp-actions no-print">
            <button type="button" onclick="window.print()" class="rcp-btn rcp-btn-primary">
                <i class="fas fa-print"></i> Print receipt
            </button>
            <a href="{{ route('sales.desk', ['shop_id' => $sale->shop_id]) }}" class="rcp-btn rcp-btn-ghost">
                <i class="fas fa-cash-register"></i> Cash Desk
            </a>
            <a href="{{ route('sales.show', $sale) }}" class="rcp-btn rcp-btn-ghost">
                <i class="fas fa-receipt"></i> View sale
            </a>
        </div>

        <article class="rcp-card">
            <div class="rcp-accent-bar"></div>

            <header class="rcp-header">
                <div class="rcp-logo"><i class="fas fa-wrench"></i></div>
                <h1 class="rcp-shop-name">{{ $sale->shop?->name ?? 'DayByDay Automotive' }}</h1>
                @if ($sale->shop?->address || $sale->shop?->phone)
                    <p class="rcp-shop-meta">
                        @if ($sale->shop?->address){{ $sale->shop->address }}<br>@endif
                        @if ($sale->shop?->phone){{ $sale->shop->phone }}@endif
                    </p>
                @endif
                <div class="rcp-receipt-no">
                    <i class="fas fa-receipt"></i>
                    {{ $sale->receipt_number }}
                </div>
            </header>

            <div class="rcp-body">
                <div class="rcp-meta-grid">
                    <div class="rcp-meta-item">
                        <p class="rcp-meta-label">Date & time</p>
                        <p class="rcp-meta-value">{{ $sale->sold_at?->format('d M Y · H:i') }}</p>
                    </div>
                    <div class="rcp-meta-item">
                        <p class="rcp-meta-label">Cashier</p>
                        <p class="rcp-meta-value">{{ $cashier?->name ?? '—' }}</p>
                    </div>
                    @if ($sale->orderedBy && $sale->ordered_by !== $sale->completed_by)
                        <div class="rcp-meta-item">
                            <p class="rcp-meta-label">Ordered by</p>
                            <p class="rcp-meta-value">{{ $sale->orderedBy->name }}</p>
                        </div>
                    @endif
                    @if ($sale->customer_name || $sale->customer_phone)
                        <div class="rcp-meta-item rcp-meta-full">
                            <p class="rcp-meta-label">Customer</p>
                            <p class="rcp-meta-value">
                                {{ $sale->customer_name ?: 'Walk-in' }}
                                @if ($sale->customer_phone) · {{ $sale->customer_phone }} @endif
                            </p>
                        </div>
                    @endif
                </div>

                <p class="rcp-section-title">Items purchased</p>
                <div class="rcp-items">
                    @foreach ($sale->items as $item)
                        <div class="rcp-line">
                            <div class="rcp-line-top">
                                <div>
                                    <p class="rcp-line-name">{{ $item->product->name }}</p>
                                    <p class="rcp-line-sku">{{ $item->product->part_number }}</p>
                                </div>
                                <span class="rcp-line-total">{{ number_format($item->line_total, 2) }}</span>
                            </div>
                            <div class="rcp-line-detail">
                                <span>{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} × KES {{ number_format($item->unit_price, 2) }}</span>
                                @if ($item->discount > 0)
                                    <span>Disc. −{{ number_format($item->discount, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="rcp-totals">
                    <div class="rcp-total-row">
                        <span>Subtotal</span>
                        <span>KES {{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    @if ($sale->discount_total > 0)
                        <div class="rcp-total-row discount">
                            <span>Discount</span>
                            <span>− KES {{ number_format($sale->discount_total, 2) }}</span>
                        </div>
                    @endif
                    @if ($sale->tax_total > 0)
                        <div class="rcp-total-row">
                            <span>Tax</span>
                            <span>KES {{ number_format($sale->tax_total, 2) }}</span>
                        </div>
                    @endif

                    <div class="rcp-grand-total">
                        <span class="rcp-grand-total-label">Total paid</span>
                        <span class="rcp-grand-total-amount">KES {{ number_format($sale->total, 2) }}</span>
                    </div>

                    <div class="rcp-paid-row">
                        <span>Amount received</span>
                        <strong>KES {{ number_format($sale->amount_paid, 2) }}</strong>
                    </div>
                    @if ($sale->change_due > 0)
                        <div class="rcp-paid-row">
                            <span>Change due</span>
                            <span class="rcp-change">KES {{ number_format($sale->change_due, 2) }}</span>
                        </div>
                    @endif
                </div>

                @if ($sale->payments->count())
                    <p class="rcp-section-title">Payment breakdown</p>
                    <div class="rcp-payments">
                        @foreach ($sale->payments as $payment)
                            @php [$icon, $iconClass] = $paymentIcon($payment->method); @endphp
                            <div class="rcp-payment-pill">
                                <div>
                                    <div class="rcp-payment-method">
                                        <span class="rcp-payment-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></span>
                                        {{ $payment->methodLabel() }}
                                    </div>
                                    @if ($payment->reference)
                                        <p class="rcp-payment-ref">Ref: {{ $payment->reference }}</p>
                                    @endif
                                </div>
                                <span class="rcp-payment-amount">KES {{ number_format($payment->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <footer class="rcp-footer">
                <p class="rcp-thanks">Thank you for your business!</p>
                <p class="rcp-footer-sub">Please retain this receipt for warranty & returns.</p>
                <p class="rcp-brand">
                    <i class="fas fa-wrench"></i> DayByDay Automotive
                </p>
            </footer>
        </article>
    </div>
</body>
</html>

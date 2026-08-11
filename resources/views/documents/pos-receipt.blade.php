<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace;
            font-size: 12px; color: #111827; margin: 0; padding: 1rem;
            background: #f3f4f6;
        }
        .receipt {
            width: 80mm; max-width: 100%; margin: 0 auto; background: #fff;
            padding: 1rem; border: 1px solid #e5e7eb;
        }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .muted { color: #6b7280; }
        hr { border: none; border-top: 1px dashed #9ca3af; margin: 0.5rem 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 0.15rem 0; vertical-align: top; }
        .right { text-align: right; }
        .totals td { padding: 0.1rem 0; }
        .logo { max-width: 120px; max-height: 60px; margin: 0 auto 0.5rem; display: block; }
        .actions { width: 80mm; max-width: 100%; margin: 1rem auto 0; display: flex; gap: 0.5rem; }
        .actions button, .actions a {
            flex: 1; text-align: center; padding: 0.6rem; border-radius: 0.5rem; border: 1px solid #d1d5db;
            background: #4f46e5; color: #fff; font-family: sans-serif; font-size: 13px; text-decoration: none; cursor: pointer;
        }
        .actions a.secondary, .actions button.secondary { background: #fff; color: #374151; }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt { border: none; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        @if($company->logo)
            <img class="logo" src="{{ Storage::url($company->logo) }}" alt="Logo">
        @endif
        <div class="center bold">{{ $company->name }}</div>
        @if($company->address)<div class="center muted">{{ $company->address }}</div>@endif
        @if($company->phone)<div class="center muted">{{ $company->phone }}</div>@endif

        <hr>

        <table>
            <tr><td>Receipt #</td><td class="right">{{ $order->order_number }}</td></tr>
            <tr><td>Date</td><td class="right">{{ $order->order_date->format('Y-m-d') }} {{ $order->created_at->format('H:i') }}</td></tr>
            <tr><td>Customer</td><td class="right">{{ $order->customer->name }}</td></tr>
            <tr><td>Warehouse</td><td class="right">{{ $order->warehouse->name }}</td></tr>
            <tr><td>Served by</td><td class="right">{{ $order->createdBy?->name ?? '-' }}</td></tr>
        </table>

        <hr>

        <table>
            @foreach($order->items as $item)
                <tr>
                    <td colspan="2">{{ $item->productVariant?->label ?? $item->product->name }}</td>
                </tr>
                <tr class="muted">
                    <td>{{ $item->quantity }} x {{ $company->currency_symbol }}{{ number_format($item->unit_price, 2) }}
                        @if($item->discount > 0) (-{{ $company->currency_symbol }}{{ number_format($item->discount, 2) }}) @endif
                    </td>
                    <td class="right">{{ $company->currency_symbol }}{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </table>

        <hr>

        <table class="totals">
            <tr><td>Subtotal</td><td class="right">{{ $company->currency_symbol }}{{ number_format($order->subtotal, 2) }}</td></tr>
            @if($order->discount_total > 0)
                <tr><td>Discount</td><td class="right">-{{ $company->currency_symbol }}{{ number_format($order->discount_total, 2) }}</td></tr>
            @endif
            @if($order->tax_total > 0)
                <tr><td>Tax</td><td class="right">{{ $company->currency_symbol }}{{ number_format($order->tax_total, 2) }}</td></tr>
            @endif
            <tr class="bold"><td>Total</td><td class="right">{{ $company->currency_symbol }}{{ number_format($order->total, 2) }}</td></tr>
        </table>

        <hr>

        <table>
            @foreach($order->payments as $payment)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                    <td class="right">{{ $company->currency_symbol }}{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @if($payment->tendered_amount)
                    <tr class="muted">
                        <td>Tendered</td>
                        <td class="right">{{ $company->currency_symbol }}{{ number_format($payment->tendered_amount, 2) }}</td>
                    </tr>
                    <tr class="muted">
                        <td>Change</td>
                        <td class="right">{{ $company->currency_symbol }}{{ number_format($payment->change_due, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

        @if($company->receipt_footer)
            <hr>
            <div class="center muted">{{ $company->receipt_footer }}</div>
        @endif

        <div class="center muted" style="margin-top:0.5rem;">Thank you!</div>
    </div>

    <div class="actions">
        <button onclick="window.print()">Print</button>
        <a href="{{ route('filament.admin.pages.point-of-sale') }}" class="secondary">New sale</a>
    </div>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $type }} {{ $number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #1f2937; }
        .header { width: 100%; margin-bottom: 24px; }
        .header td { vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: #111827; }
        .muted { color: #6b7280; }
        .doc-title { font-size: 22px; font-weight: bold; color: #4f46e5; text-align: right; }
        .doc-meta { text-align: right; margin-top: 6px; }
        .logo { max-width: 140px; max-height: 70px; }

        .party-box { width: 100%; margin-bottom: 20px; }
        .party-box td { vertical-align: top; width: 50%; }
        .party-label { font-size: 10px; text-transform: uppercase; color: #4b5563; letter-spacing: 0.05em; margin-bottom: 4px; }
        .party-name { font-weight: bold; font-size: 13px; color: #111827; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th {
            background: #4f46e5; color: #fff; text-align: left; padding: 8px 10px; font-size: 10px;
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        table.items .right { text-align: right; }

        table.totals { width: 260px; margin-left: auto; border-collapse: collapse; }
        table.totals td { padding: 4px 0; font-size: 12px; }
        table.totals .right { text-align: right; }
        table.totals .grand td { font-size: 15px; font-weight: bold; border-top: 2px solid #111827; padding-top: 8px; color: #111827; }

        .status-badge {
            display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold;
            text-transform: uppercase; background: #dcfce7; color: #166534;
        }
        .status-badge.unpaid { background: #fee2e2; color: #991b1b; }
        .status-badge.partial { background: #fef3c7; color: #92400e; }

        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #6b7280; }
        .notes { margin-top: 16px; font-size: 11px; color: #4b5563; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 60%;">
                @if($company->logo)
                    <img class="logo" src="{{ public_path('storage/'.$company->logo) }}">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                @if($company->address)<div class="muted">{{ $company->address }}</div>@endif
                @if($company->phone)<div class="muted">{{ $company->phone }}</div>@endif
                @if($company->email)<div class="muted">{{ $company->email }}</div>@endif
                @if($company->tax_id)<div class="muted">Tax ID: {{ $company->tax_id }}</div>@endif
            </td>
            <td style="width: 40%;">
                <div class="doc-title">{{ strtoupper($type) }}</div>
                <div class="doc-meta">
                    <div><strong>#</strong> {{ $number }}</div>
                    <div class="muted">{{ \Illuminate\Support\Carbon::parse($date)->format('F j, Y') }}</div>
                    <div style="margin-top:6px;">
                        <span class="status-badge {{ $paymentStatus }}">{{ ucfirst($paymentStatus) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="party-box">
        <tr>
            <td>
                <div class="party-label">{{ $partyLabel }}</div>
                <div class="party-name">{{ $partyName }}</div>
                @if($partyDetails)<div class="muted">{{ $partyDetails }}</div>@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Unit price</th>
                <th class="right">Discount</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td>{{ $line['name'] }}<br><span class="muted">{{ $line['sku'] }}</span></td>
                    <td class="right">{{ $line['quantity'] }}</td>
                    <td class="right">{{ $company->currency_symbol }}{{ number_format($line['unit_price'], 2) }}</td>
                    <td class="right">{{ $line['discount'] > 0 ? $company->currency_symbol.number_format($line['discount'], 2) : '-' }}</td>
                    <td class="right">{{ $company->currency_symbol }}{{ number_format($line['subtotal'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ $company->currency_symbol }}{{ number_format($subtotal, 2) }}</td></tr>
        @if($discountTotal > 0)
            <tr><td>Discount</td><td class="right">-{{ $company->currency_symbol }}{{ number_format($discountTotal, 2) }}</td></tr>
        @endif
        @if($taxTotal > 0)
            <tr><td>Tax</td><td class="right">{{ $company->currency_symbol }}{{ number_format($taxTotal, 2) }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="right">{{ $company->currency_symbol }}{{ number_format($total, 2) }}</td></tr>
        <tr><td>Amount paid</td><td class="right">{{ $company->currency_symbol }}{{ number_format($amountPaid, 2) }}</td></tr>
        <tr><td>Balance due</td><td class="right">{{ $company->currency_symbol }}{{ number_format($total - $amountPaid, 2) }}</td></tr>
    </table>

    @if($notes)
        <div class="notes"><strong>Notes:</strong> {{ $notes }}</div>
    @endif

    <div class="footer">
        {{ $company->invoice_footer ?: 'Thank you for your business.' }}
    </div>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Product Labels</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; background: #f3f4f6; margin: 0; padding: 1.5rem; }
        .toolbar { max-width: 900px; margin: 0 auto 1rem; display: flex; justify-content: flex-end; gap: 0.5rem; }
        .toolbar button, .toolbar a {
            padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; background: #4f46e5; color: #fff;
            font-size: 13px; text-decoration: none; cursor: pointer;
        }
        .toolbar a.secondary { background: #fff; color: #374151; }

        .sheet { max-width: 900px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .label {
            border: 1px dashed #9ca3af; border-radius: 4px; padding: 8px; background: #fff;
            display: flex; flex-direction: column; align-items: center; text-align: center; gap: 2px;
            page-break-inside: avoid;
        }
        .label .name { font-size: 10px; font-weight: 700; color: #111827; line-height: 1.2; min-height: 24px; }
        .label .price { font-size: 12px; font-weight: 700; color: #4f46e5; }
        .label .sku { font-size: 8px; color: #6b7280; }
        .label svg { max-width: 100%; height: 34px; }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .sheet { gap: 4px; }
            .label { border: 1px solid #d1d5db; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print</button>
        <a href="{{ route('filament.admin.resources.products.index') }}" class="secondary">Back to products</a>
    </div>

    <div class="sheet">
        @php $barcodeService = app(\App\Services\BarcodeService::class); @endphp
        @foreach($labels as $product)
            <div class="label">
                <div class="name">{{ $product->name }}</div>
                {!! $barcodeService->code128Svg($product->barcode ?: $product->sku, 1, 34) !!}
                <div class="sku">{{ $product->sku }}</div>
                <div class="price">{{ $company->currency_symbol }}{{ number_format($product->selling_price, 2) }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>

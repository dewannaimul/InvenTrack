@php
    $product = $getRecord();
    $barcodeService = app(\App\Services\BarcodeService::class);
    $code = $product->barcode ?: $product->sku;
@endphp

<div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap:wrap;">
    <div>
        <div style="font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.375rem;">Barcode ({{ $code }})</div>
        <div style="background:#fff; padding:0.75rem; border-radius:0.5rem; display:inline-block;">
            {!! $barcodeService->code128Svg($code) !!}
        </div>
    </div>
    <div>
        <div style="font-size:0.75rem; font-weight:600; color:#6b7280; margin-bottom:0.375rem;">QR code (SKU)</div>
        <div style="background:#fff; padding:0.75rem; border-radius:0.5rem; display:inline-block;">
            {!! $barcodeService->qrCodeSvg($product->sku) !!}
        </div>
    </div>
</div>

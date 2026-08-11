<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfInstance;

class InvoiceService
{
    public function salesInvoicePdf(SalesOrder $salesOrder): PdfInstance
    {
        $salesOrder->loadMissing(['items.product', 'items.productVariant', 'customer', 'warehouse', 'payments']);

        $lines = $salesOrder->items->map(fn ($item) => [
            'name' => $item->productVariant?->label ?? $item->product->name,
            'sku' => $item->productVariant?->sku ?? $item->product->sku,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'discount' => (float) $item->discount,
            'subtotal' => (float) $item->subtotal,
        ]);

        return Pdf::loadView('documents.invoice', [
            'type' => 'Sales Invoice',
            'number' => $salesOrder->order_number,
            'date' => $salesOrder->order_date,
            'partyLabel' => 'Bill to',
            'partyName' => $salesOrder->customer->name,
            'partyDetails' => $salesOrder->customer->address,
            'lines' => $lines,
            'subtotal' => (float) $salesOrder->subtotal,
            'discountTotal' => (float) $salesOrder->discount_total,
            'taxTotal' => (float) $salesOrder->tax_total,
            'total' => (float) $salesOrder->total,
            'amountPaid' => (float) $salesOrder->amount_paid,
            'paymentStatus' => $salesOrder->payment_status,
            'notes' => $salesOrder->notes,
            'company' => CompanySetting::current(),
        ]);
    }

    public function purchaseInvoicePdf(PurchaseOrder $purchaseOrder): PdfInstance
    {
        $purchaseOrder->loadMissing(['items.product', 'items.productVariant', 'supplier', 'warehouse', 'payments']);

        $lines = $purchaseOrder->items->map(fn ($item) => [
            'name' => $item->productVariant?->label ?? $item->product->name,
            'sku' => $item->productVariant?->sku ?? $item->product->sku,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_cost,
            'discount' => 0.0,
            'subtotal' => (float) $item->subtotal,
        ]);

        return Pdf::loadView('documents.invoice', [
            'type' => 'Purchase Order',
            'number' => $purchaseOrder->po_number,
            'date' => $purchaseOrder->order_date,
            'partyLabel' => 'Supplier',
            'partyName' => $purchaseOrder->supplier->name,
            'partyDetails' => $purchaseOrder->supplier->address,
            'lines' => $lines,
            'subtotal' => (float) $purchaseOrder->subtotal,
            'discountTotal' => (float) $purchaseOrder->discount_total,
            'taxTotal' => (float) $purchaseOrder->tax_total,
            'total' => (float) $purchaseOrder->total,
            'amountPaid' => (float) $purchaseOrder->amount_paid,
            'paymentStatus' => $purchaseOrder->payment_status,
            'notes' => $purchaseOrder->notes,
            'company' => CompanySetting::current(),
        ]);
    }
}

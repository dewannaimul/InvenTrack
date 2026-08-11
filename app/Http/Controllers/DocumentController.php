<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(protected InvoiceService $invoices)
    {
        abort_unless(auth()->check(), 403);
    }

    public function productLabels(Request $request): View
    {
        $ids = array_filter(explode(',', (string) $request->query('ids')));
        $copies = max(1, (int) $request->query('qty', 1));

        $products = Product::query()->whereIn('id', $ids)->get();

        $labels = collect();

        foreach ($products as $product) {
            for ($i = 0; $i < $copies; $i++) {
                $labels->push($product);
            }
        }

        return view('documents.product-labels', [
            'labels' => $labels,
            'company' => CompanySetting::current(),
        ]);
    }

    public function posReceipt(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['items.product', 'items.productVariant', 'customer', 'warehouse', 'payments']);

        return view('documents.pos-receipt', [
            'order' => $salesOrder,
            'company' => CompanySetting::current(),
        ]);
    }

    public function salesInvoice(SalesOrder $salesOrder)
    {
        return $this->invoices->salesInvoicePdf($salesOrder)->download("invoice-{$salesOrder->order_number}.pdf");
    }

    public function purchaseInvoice(PurchaseOrder $purchaseOrder)
    {
        return $this->invoices->purchaseInvoicePdf($purchaseOrder)->download("po-{$purchaseOrder->po_number}.pdf");
    }
}

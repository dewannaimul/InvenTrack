<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportExportController extends Controller
{
    public function __construct(protected ReportService $reports)
    {
        abort_unless(auth()->check(), 403);
    }

    protected function range(Request $request): array
    {
        return [
            Carbon::parse($request->query('from', now()->subDays(29)->toDateString()))->startOfDay(),
            Carbon::parse($request->query('to', now()->toDateString()))->endOfDay(),
        ];
    }

    protected function streamCsv(string $filename, array $headers, iterable $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename.'-'.now()->format('Ymd-His').'.csv');
    }

    public function dailySales(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->reports->dailySales($from, $to)->map(fn ($r) => [$r->day, $r->orders, number_format($r->revenue, 2)]);

        return $this->streamCsv('sales-by-day', ['Date', 'Orders', 'Revenue'], $rows);
    }

    public function topProducts(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->reports->topProducts($from, $to, 100000)->map(fn ($r) => [$r->sku, $r->name, $r->qty_sold, number_format($r->revenue, 2)]);

        return $this->streamCsv('top-products', ['SKU', 'Product', 'Qty sold', 'Revenue'], $rows);
    }

    public function topCustomers(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->reports->topCustomers($from, $to, 100000)->map(fn ($r) => [
            $r->name, $r->orders, number_format($r->revenue, 2), number_format($r->avg_order_value, 2), number_format($r->outstanding, 2),
        ]);

        return $this->streamCsv('top-customers', ['Customer', 'Orders', 'Revenue', 'Avg order value', 'Outstanding'], $rows);
    }

    public function salesByStaff(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->reports->salesByStaff($from, $to)->map(fn ($r) => [$r->name, $r->orders, number_format($r->revenue, 2)]);

        return $this->streamCsv('sales-by-staff', ['Staff', 'Orders', 'Revenue'], $rows);
    }

    public function margin(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->reports->marginByProduct($from, $to, 100000)->map(fn ($r) => [
            $r->sku, $r->name, $r->qty_sold, number_format($r->revenue, 2), number_format($r->cost, 2), number_format($r->margin, 2),
        ]);

        return $this->streamCsv('profit-margin', ['SKU', 'Product', 'Qty sold', 'Revenue', 'Cost', 'Margin'], $rows);
    }

    public function inventoryValuation()
    {
        $rows = $this->reports->inventoryValuation()->map(fn ($r) => [
            $r->sku, $r->name, $r->warehouse_name, $r->quantity, number_format($r->cost_price, 2), number_format($r->value, 2),
        ]);

        return $this->streamCsv('inventory-valuation', ['SKU', 'Product', 'Warehouse', 'Qty', 'Unit cost', 'Value'], $rows);
    }

    public function lowStock()
    {
        $rows = $this->reports->lowStockProducts()->map(fn ($p) => [
            $p->sku, $p->name, (int) ($p->stock_quantity ?? 0), $p->reorder_level,
        ]);

        return $this->streamCsv('low-stock', ['SKU', 'Product', 'In stock', 'Reorder level'], $rows);
    }

    public function spendBySupplier(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->reports->spendBySupplier($from, $to)->map(fn ($r) => [
            $r->name, $r->orders, number_format($r->spend, 2), number_format($r->outstanding, 2),
        ]);

        return $this->streamCsv('spend-by-supplier', ['Supplier', 'Orders', 'Spend', 'Outstanding'], $rows);
    }

    public function outstandingPurchaseOrders()
    {
        $rows = $this->reports->outstandingPurchaseOrders()->map(fn ($po) => [
            $po->po_number, $po->supplier->name, $po->status, $po->expected_date?->format('Y-m-d'), number_format($po->total, 2),
        ]);

        return $this->streamCsv('outstanding-purchase-orders', ['PO #', 'Supplier', 'Status', 'Expected date', 'Total'], $rows);
    }
}

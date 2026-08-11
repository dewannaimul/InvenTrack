<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Stock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected function baseSalesQuery(Carbon $from, Carbon $to)
    {
        return SalesOrder::query()
            ->whereNotIn('status', [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CANCELLED])
            ->whereBetween('order_date', [$from, $to]);
    }

    protected function basePurchaseQuery(Carbon $from, Carbon $to)
    {
        return PurchaseOrder::query()
            ->where('status', '!=', PurchaseOrder::STATUS_CANCELLED)
            ->whereBetween('order_date', [$from, $to]);
    }

    public function salesSummary(Carbon $from, Carbon $to): array
    {
        $query = $this->baseSalesQuery($from, $to);

        return [
            'order_count' => (clone $query)->count(),
            'revenue' => (float) (clone $query)->sum('total'),
            'tax_collected' => (float) (clone $query)->sum('tax_total'),
            'avg_order_value' => (float) (clone $query)->avg('total'),
        ];
    }

    public function dailySales(Carbon $from, Carbon $to)
    {
        return $this->baseSalesQuery($from, $to)
            ->selectRaw('DATE(order_date) as day, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    public function topProducts(Carbon $from, Carbon $to, int $limit = 10)
    {
        return DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('products', 'products.id', '=', 'sales_order_items.product_id')
            ->whereNotIn('sales_orders.status', [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CANCELLED])
            ->whereBetween('sales_orders.order_date', [$from, $to])
            ->selectRaw('products.name, products.sku, SUM(sales_order_items.quantity) as qty_sold, SUM(sales_order_items.subtotal) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function topCustomers(Carbon $from, Carbon $to, int $limit = 10)
    {
        return $this->baseSalesQuery($from, $to)
            ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
            ->selectRaw('customers.name, COUNT(*) as orders, SUM(sales_orders.total) as revenue, AVG(sales_orders.total) as avg_order_value, SUM(sales_orders.total - sales_orders.amount_paid) as outstanding')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function salesByStaff(Carbon $from, Carbon $to)
    {
        return $this->baseSalesQuery($from, $to)
            ->join('users', 'users.id', '=', 'sales_orders.created_by')
            ->selectRaw('users.name, COUNT(*) as orders, SUM(sales_orders.total) as revenue')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->get();
    }

    public function marginByProduct(Carbon $from, Carbon $to, int $limit = 15)
    {
        return DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('products', 'products.id', '=', 'sales_order_items.product_id')
            ->whereNotIn('sales_orders.status', [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CANCELLED])
            ->whereBetween('sales_orders.order_date', [$from, $to])
            ->selectRaw('products.name, products.sku, SUM(sales_order_items.quantity) as qty_sold,
                SUM(sales_order_items.subtotal) as revenue,
                SUM(sales_order_items.quantity * products.cost_price) as cost,
                SUM(sales_order_items.subtotal) - SUM(sales_order_items.quantity * products.cost_price) as margin')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('margin')
            ->limit($limit)
            ->get();
    }

    public function inventoryValuation(?int $limit = null)
    {
        $query = Stock::query()
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'stocks.warehouse_id')
            ->selectRaw('products.name, products.sku, warehouses.name as warehouse_name, stocks.quantity, products.cost_price, (stocks.quantity * products.cost_price) as value')
            ->where('stocks.quantity', '>', 0)
            ->orderByDesc('value');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function inventoryValuationTotal(): float
    {
        return (float) Stock::query()
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->sum(DB::raw('stocks.quantity * products.cost_price'));
    }

    public function lowStockProducts(?int $limit = null)
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->withSum('stocks as stock_quantity', 'quantity')
            ->get()
            ->filter(fn (Product $p) => (int) ($p->stock_quantity ?? 0) <= $p->reorder_level)
            ->sortBy('stock_quantity');

        return $limit ? $products->take($limit) : $products;
    }

    public function spendBySupplier(Carbon $from, Carbon $to)
    {
        return $this->basePurchaseQuery($from, $to)
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->selectRaw('suppliers.name, COUNT(*) as orders, SUM(purchase_orders.total) as spend, SUM(purchase_orders.total - purchase_orders.amount_paid) as outstanding')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('spend')
            ->get();
    }

    public function outstandingPurchaseOrders()
    {
        return PurchaseOrder::query()
            ->whereIn('status', [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])
            ->with('supplier')
            ->orderBy('expected_date')
            ->get();
    }
}

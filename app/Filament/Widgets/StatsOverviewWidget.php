<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalProducts = Product::query()->where('is_active', true)->count();

        $stockValue = Stock::query()
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->sum(DB::raw('stocks.quantity * products.cost_price'));

        $lowStockCount = Product::query()
            ->where('track_stock', true)
            ->withSum('stocks as stock_quantity', 'quantity')
            ->get()
            ->filter(fn (Product $product) => (int) ($product->stock_quantity ?? 0) <= $product->reorder_level)
            ->count();

        $pendingPurchaseOrders = PurchaseOrder::query()
            ->whereIn('status', [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])
            ->count();

        $todaysSales = SalesOrder::query()
            ->whereDate('order_date', now())
            ->whereNotIn('status', [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CANCELLED])
            ->sum('total');

        return [
            Stat::make('Active products', number_format($totalProducts))
                ->icon('heroicon-o-cube')
                ->color('info'),
            Stat::make('Stock value', '$'.number_format($stockValue, 2))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Low stock items', number_format($lowStockCount))
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
            Stat::make('Pending purchase orders', number_format($pendingPurchaseOrders))
                ->icon('heroicon-o-shopping-cart')
                ->color('warning'),
            Stat::make("Today's sales", '$'.number_format($todaysSales, 2))
                ->icon('heroicon-o-chart-bar')
                ->color('primary'),
        ];
    }
}

<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class Reports extends Page
{
    protected string $view = 'filament.pages.reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = -5;

    protected Width|string|null $maxContentWidth = Width::Full;

    public string $dateFrom;

    public string $dateTo;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:Reports') ?? false;
    }

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    protected function from(): Carbon
    {
        return Carbon::parse($this->dateFrom)->startOfDay();
    }

    protected function to(): Carbon
    {
        return Carbon::parse($this->dateTo)->endOfDay();
    }

    protected function reports(): ReportService
    {
        return app(ReportService::class);
    }

    public function getSalesSummaryProperty(): array
    {
        return $this->reports()->salesSummary($this->from(), $this->to());
    }

    public function getDailySalesProperty()
    {
        return $this->reports()->dailySales($this->from(), $this->to());
    }

    public function getTopProductsProperty()
    {
        return $this->reports()->topProducts($this->from(), $this->to());
    }

    public function getTopCustomersProperty()
    {
        return $this->reports()->topCustomers($this->from(), $this->to());
    }

    public function getSalesByStaffProperty()
    {
        return $this->reports()->salesByStaff($this->from(), $this->to());
    }

    public function getMarginByProductProperty()
    {
        return $this->reports()->marginByProduct($this->from(), $this->to());
    }

    public function getInventoryValuationProperty()
    {
        return $this->reports()->inventoryValuation(15);
    }

    public function getInventoryValuationTotalProperty(): float
    {
        return $this->reports()->inventoryValuationTotal();
    }

    public function getLowStockProductsProperty()
    {
        return $this->reports()->lowStockProducts(20);
    }

    public function getSpendBySupplierProperty()
    {
        return $this->reports()->spendBySupplier($this->from(), $this->to());
    }

    public function getOutstandingPurchaseOrdersProperty()
    {
        return $this->reports()->outstandingPurchaseOrders();
    }
}

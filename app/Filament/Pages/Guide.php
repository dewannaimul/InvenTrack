<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Guide extends Page
{
    protected string $view = 'filament.pages.guide';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 100;

    public static function getNavigationLabel(): string
    {
        return 'Getting Started';
    }

    public function getTitle(): string
    {
        return 'Getting Started Guide';
    }

    public function getChecklistProperty(): array
    {
        return [
            [
                'label' => 'Set up your warehouses',
                'done' => Warehouse::query()->exists(),
                'url' => route('filament.admin.resources.warehouses.index'),
            ],
            [
                'label' => 'Create at least one category',
                'done' => Category::query()->exists(),
                'url' => route('filament.admin.resources.categories.index'),
            ],
            [
                'label' => 'Add your first product',
                'done' => Product::query()->exists(),
                'url' => route('filament.admin.resources.products.index'),
            ],
            [
                'label' => 'Create and receive a purchase order to bring in stock',
                'done' => PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_RECEIVED)->exists()
                    || PurchaseOrder::query()->where('status', PurchaseOrder::STATUS_PARTIALLY_RECEIVED)->exists(),
                'url' => route('filament.admin.resources.purchase-orders.index'),
            ],
            [
                'label' => 'Complete your first sale (POS or back office)',
                'done' => SalesOrder::query()->where('status', SalesOrder::STATUS_COMPLETED)->exists(),
                'url' => route('filament.admin.pages.point-of-sale'),
            ],
            [
                'label' => 'Invite your team and set roles',
                'done' => User::query()->count() > 1,
                'url' => route('filament.admin.resources.users.index'),
            ],
            [
                'label' => 'Fill in your business profile for invoices/receipts',
                'done' => \App\Models\CompanySetting::current()->name !== 'My Company',
                'url' => route('filament.admin.pages.company-settings'),
            ],
        ];
    }

    public function getChecklistProgressProperty(): array
    {
        $items = $this->checklist;
        $done = collect($items)->where('done', true)->count();

        return ['done' => $done, 'total' => count($items)];
    }
}

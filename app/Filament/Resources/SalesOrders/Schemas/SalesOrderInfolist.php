<?php

namespace App\Filament\Resources\SalesOrders\Schemas;

use App\Models\SalesOrder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SalesOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order info')
                    ->columns(3)
                    ->components([
                        TextEntry::make('order_number')->label('Order number'),
                        TextEntry::make('customer.name')->label('Customer'),
                        TextEntry::make('warehouse.name')->label('Warehouse'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                SalesOrder::STATUS_DRAFT => 'gray',
                                SalesOrder::STATUS_CONFIRMED => 'info',
                                SalesOrder::STATUS_SHIPPED => 'warning',
                                SalesOrder::STATUS_COMPLETED => 'success',
                                SalesOrder::STATUS_CANCELLED, SalesOrder::STATUS_RETURNED => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('order_date')->date(),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ]),
                Section::make('Items')
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')->label('Product'),
                                TextEntry::make('quantity')->numeric(),
                                TextEntry::make('unit_price')->money('usd'),
                                TextEntry::make('subtotal')->money('usd'),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Totals')
                    ->columns(4)
                    ->components([
                        TextEntry::make('subtotal')->money('usd'),
                        TextEntry::make('tax_total')->money('usd'),
                        TextEntry::make('discount_total')->money('usd'),
                        TextEntry::make('total')->money('usd')->weight('bold'),
                        TextEntry::make('amount_paid')->money('usd'),
                        TextEntry::make('balance_due')->money('usd')->label('Balance due'),
                        TextEntry::make('payment_status')->badge(),
                    ]),
            ]);
    }
}

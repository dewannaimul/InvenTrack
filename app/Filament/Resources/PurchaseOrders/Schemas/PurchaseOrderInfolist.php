<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\PurchaseOrder;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order info')
                    ->columns(3)
                    ->components([
                        TextEntry::make('po_number')->label('PO number'),
                        TextEntry::make('supplier.name')->label('Supplier'),
                        TextEntry::make('warehouse.name')->label('Warehouse'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                PurchaseOrder::STATUS_DRAFT => 'gray',
                                PurchaseOrder::STATUS_ORDERED => 'info',
                                PurchaseOrder::STATUS_PARTIALLY_RECEIVED => 'warning',
                                PurchaseOrder::STATUS_RECEIVED => 'success',
                                PurchaseOrder::STATUS_CANCELLED => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('order_date')->date(),
                        TextEntry::make('expected_date')->date()->placeholder('-'),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ]),
                Section::make('Items')
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')->label('Product'),
                                TextEntry::make('quantity')->numeric(),
                                TextEntry::make('received_quantity')->label('Received'),
                                TextEntry::make('unit_cost')->money('usd'),
                                TextEntry::make('subtotal')->money('usd'),
                            ])
                            ->columns(5),
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

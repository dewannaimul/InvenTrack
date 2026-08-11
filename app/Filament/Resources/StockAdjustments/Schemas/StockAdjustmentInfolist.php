<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\StockAdjustment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockAdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Adjustment info')
                    ->columns(3)
                    ->components([
                        TextEntry::make('adjustment_number'),
                        TextEntry::make('warehouse.name')->label('Warehouse'),
                        TextEntry::make('reason')->badge(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => $state === StockAdjustment::STATUS_APPLIED ? 'success' : 'gray'),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ]),
                Section::make('Items')
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')->label('Product'),
                                TextEntry::make('quantity_before')->label('Before'),
                                TextEntry::make('quantity_after')->label('After'),
                                TextEntry::make('difference'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\StockTransfers\Schemas;

use App\Models\StockTransfer;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockTransferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transfer info')
                    ->columns(3)
                    ->components([
                        TextEntry::make('transfer_number'),
                        TextEntry::make('fromWarehouse.name')->label('From warehouse'),
                        TextEntry::make('toWarehouse.name')->label('To warehouse'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                StockTransfer::STATUS_PENDING => 'gray',
                                StockTransfer::STATUS_IN_TRANSIT => 'warning',
                                StockTransfer::STATUS_COMPLETED => 'success',
                                StockTransfer::STATUS_CANCELLED => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('transfer_date')->date(),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ]),
                Section::make('Items')
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')->label('Product'),
                                TextEntry::make('quantity')->numeric(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}

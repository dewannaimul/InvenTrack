<?php

namespace App\Filament\Resources\Stocks;

use App\Filament\Resources\Stocks\Pages\ListStocks;
use App\Filament\Resources\Stocks\Tables\StocksTable;
use App\Models\Stock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Stock levels';

    protected static ?string $modelLabel = 'stock level';

    public static function table(Table $table): Table
    {
        return StocksTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
        ];
    }
}

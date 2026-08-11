<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low stock products';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Product::query()
                    ->where('is_active', true)
                    ->where('track_stock', true)
                    ->withSum('stocks as stock_quantity', 'quantity')
                    ->havingRaw('COALESCE(stock_quantity, 0) <= reorder_level')
            )
            ->columns([
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('name'),
                TextColumn::make('category.name'),
                TextColumn::make('stock_quantity')
                    ->label('In stock')
                    ->state(fn ($record) => (int) ($record->stock_quantity ?? 0))
                    ->badge()
                    ->color('danger'),
                TextColumn::make('reorder_level')
                    ->label('Reorder at'),
            ])
            ->paginated([5, 10, 25])
            ->defaultSort('stock_quantity');
    }
}

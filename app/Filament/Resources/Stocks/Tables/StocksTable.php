<?php

namespace App\Filament\Resources\Stocks\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('productVariant.sku')
                    ->label('Variant')
                    ->placeholder('-'),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('On hand')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->quantity <= $record->product->reorder_level ? 'danger' : 'success'),
                TextColumn::make('reserved_quantity')
                    ->label('Reserved')
                    ->numeric(),
                TextColumn::make('available_quantity')
                    ->label('Available')
                    ->state(fn ($record) => $record->quantity - $record->reserved_quantity)
                    ->numeric(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Last updated'),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Warehouse'),
                Filter::make('low_stock')
                    ->label('Low stock only')
                    ->query(fn (Builder $query) => $query->whereHas('product', fn ($q) => $q->whereColumn('stocks.quantity', '<=', 'products.reorder_level'))),
            ])
            ->defaultSort('quantity');
    }
}

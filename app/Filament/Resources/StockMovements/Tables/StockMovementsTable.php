<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\StockMovement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('productVariant.sku')
                    ->label('Variant')
                    ->placeholder('-'),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        StockMovement::TYPE_PURCHASE, StockMovement::TYPE_TRANSFER_IN, StockMovement::TYPE_RETURN => 'success',
                        StockMovement::TYPE_SALE, StockMovement::TYPE_TRANSFER_OUT => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->numeric()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ($state >= 0 ? '+' : '').$state),
                TextColumn::make('quantity_after')
                    ->label('Balance after')
                    ->numeric(),
                TextColumn::make('note')
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('System'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        StockMovement::TYPE_PURCHASE => 'Purchase',
                        StockMovement::TYPE_SALE => 'Sale',
                        StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                        StockMovement::TYPE_TRANSFER_IN => 'Transfer in',
                        StockMovement::TYPE_TRANSFER_OUT => 'Transfer out',
                        StockMovement::TYPE_RETURN => 'Return',
                    ]),
                SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Warehouse'),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

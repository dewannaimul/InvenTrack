<?php

namespace App\Filament\Resources\StockAdjustments\Tables;

use App\Filament\Resources\StockAdjustments\Actions\ApplyAdjustmentAction;
use App\Models\StockAdjustment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('adjustment_number')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('reason')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === StockAdjustment::STATUS_APPLIED ? 'success' : 'gray'),
                TextColumn::make('createdBy.name')
                    ->label('Created by')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        StockAdjustment::STATUS_DRAFT => 'Draft',
                        StockAdjustment::STATUS_APPLIED => 'Applied',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ApplyAdjustmentAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

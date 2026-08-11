<?php

namespace App\Filament\Resources\StockTransfers\Tables;

use App\Filament\Resources\StockTransfers\Actions\CompleteTransferAction;
use App\Models\StockTransfer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transfer_number')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('fromWarehouse.name')
                    ->label('From')
                    ->searchable(),
                TextColumn::make('toWarehouse.name')
                    ->label('To')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        StockTransfer::STATUS_PENDING => 'gray',
                        StockTransfer::STATUS_IN_TRANSIT => 'warning',
                        StockTransfer::STATUS_COMPLETED => 'success',
                        StockTransfer::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('transfer_date')
                    ->date()
                    ->sortable(),
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
                        StockTransfer::STATUS_PENDING => 'Pending',
                        StockTransfer::STATUS_IN_TRANSIT => 'In transit',
                        StockTransfer::STATUS_COMPLETED => 'Completed',
                        StockTransfer::STATUS_CANCELLED => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                CompleteTransferAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources\SalesOrders\Tables;

use App\Filament\Actions\ExportCsvBulkAction;
use App\Filament\Resources\SalesOrders\Actions\ConfirmOrderAction;
use App\Models\SalesOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state) => $state === SalesOrder::SOURCE_POS ? 'primary' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === SalesOrder::SOURCE_POS ? 'POS' : 'Back office'),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        SalesOrder::STATUS_DRAFT => 'gray',
                        SalesOrder::STATUS_CONFIRMED => 'info',
                        SalesOrder::STATUS_SHIPPED => 'warning',
                        SalesOrder::STATUS_COMPLETED => 'success',
                        SalesOrder::STATUS_CANCELLED => 'danger',
                        SalesOrder::STATUS_RETURNED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        SalesOrder::STATUS_DRAFT => 'Draft',
                        SalesOrder::STATUS_CONFIRMED => 'Confirmed',
                        SalesOrder::STATUS_SHIPPED => 'Shipped',
                        SalesOrder::STATUS_COMPLETED => 'Completed',
                        SalesOrder::STATUS_CANCELLED => 'Cancelled',
                        SalesOrder::STATUS_RETURNED => 'Returned',
                    ]),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('source')
                    ->options([
                        SalesOrder::SOURCE_POS => 'POS',
                        SalesOrder::SOURCE_BACKOFFICE => 'Back office',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ConfirmOrderAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportCsvBulkAction::make('sales-orders', [
                        'Order #' => 'order_number',
                        'Source' => 'source',
                        'Customer' => 'customer.name',
                        'Warehouse' => 'warehouse.name',
                        'Status' => 'status',
                        'Order date' => fn ($r) => $r->order_date?->format('Y-m-d'),
                        'Total' => 'total',
                        'Payment status' => 'payment_status',
                    ]),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

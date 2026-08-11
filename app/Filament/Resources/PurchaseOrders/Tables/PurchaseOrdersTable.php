<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Actions\ExportCsvBulkAction;
use App\Filament\Resources\PurchaseOrders\Actions\ReceiveStockAction;
use App\Models\PurchaseOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO number')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        PurchaseOrder::STATUS_DRAFT => 'gray',
                        PurchaseOrder::STATUS_ORDERED => 'info',
                        PurchaseOrder::STATUS_PARTIALLY_RECEIVED => 'warning',
                        PurchaseOrder::STATUS_RECEIVED => 'success',
                        PurchaseOrder::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
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
                        PurchaseOrder::STATUS_DRAFT => 'Draft',
                        PurchaseOrder::STATUS_ORDERED => 'Ordered',
                        PurchaseOrder::STATUS_PARTIALLY_RECEIVED => 'Partially received',
                        PurchaseOrder::STATUS_RECEIVED => 'Received',
                        PurchaseOrder::STATUS_CANCELLED => 'Cancelled',
                    ]),
                SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ReceiveStockAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportCsvBulkAction::make('purchase-orders', [
                        'PO #' => 'po_number',
                        'Supplier' => 'supplier.name',
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

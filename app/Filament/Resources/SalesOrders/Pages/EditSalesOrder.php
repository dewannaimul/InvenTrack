<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesOrders\Actions\CancelOrderAction;
use App\Filament\Resources\SalesOrders\Actions\ConfirmOrderAction;
use App\Filament\Resources\SalesOrders\Concerns\ComputesSalesOrderTotals;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Models\SalesOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesOrder extends EditRecord
{
    use ComputesSalesOrderTotals;

    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ConfirmOrderAction::make(),
            Action::make('shipOrder')
                ->label('Mark as shipped')
                ->icon('heroicon-o-truck')
                ->visible(fn (SalesOrder $record) => $record->status === SalesOrder::STATUS_CONFIRMED)
                ->action(fn (SalesOrder $record) => $record->update(['status' => SalesOrder::STATUS_SHIPPED])),
            Action::make('completeOrder')
                ->label('Mark as completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (SalesOrder $record) => $record->status === SalesOrder::STATUS_SHIPPED)
                ->action(fn (SalesOrder $record) => $record->update(['status' => SalesOrder::STATUS_COMPLETED])),
            CancelOrderAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->computeTotals($data);
    }
}

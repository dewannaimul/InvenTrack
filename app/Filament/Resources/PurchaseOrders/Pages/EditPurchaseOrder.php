<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\Actions\ReceiveStockAction;
use App\Filament\Resources\PurchaseOrders\Concerns\ComputesPurchaseOrderTotals;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    use ComputesPurchaseOrderTotals;

    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReceiveStockAction::make(),
            Action::make('markAsOrdered')
                ->label('Mark as ordered')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (PurchaseOrder $record) => $record->status === PurchaseOrder::STATUS_DRAFT)
                ->requiresConfirmation()
                ->action(fn (PurchaseOrder $record) => $record->update(['status' => PurchaseOrder::STATUS_ORDERED])),
            Action::make('cancel')
                ->label('Cancel order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (PurchaseOrder $record) => ! in_array($record->status, [PurchaseOrder::STATUS_RECEIVED, PurchaseOrder::STATUS_CANCELLED]))
                ->requiresConfirmation()
                ->action(fn (PurchaseOrder $record) => $record->update(['status' => PurchaseOrder::STATUS_CANCELLED])),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->computeTotals($data);
    }
}

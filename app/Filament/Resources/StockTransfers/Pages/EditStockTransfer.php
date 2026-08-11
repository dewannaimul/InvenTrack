<?php

namespace App\Filament\Resources\StockTransfers\Pages;

use App\Filament\Resources\StockTransfers\Actions\CompleteTransferAction;
use App\Filament\Resources\StockTransfers\StockTransferResource;
use App\Models\StockTransfer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStockTransfer extends EditRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CompleteTransferAction::make(),
            Action::make('cancelTransfer')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (StockTransfer $record) => in_array($record->status, [StockTransfer::STATUS_PENDING, StockTransfer::STATUS_IN_TRANSIT]))
                ->requiresConfirmation()
                ->action(fn (StockTransfer $record) => $record->update(['status' => StockTransfer::STATUS_CANCELLED])),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['created_by'] ??= auth()->id();

        return $data;
    }
}

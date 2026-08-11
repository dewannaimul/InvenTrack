<?php

namespace App\Filament\Resources\StockTransfers\Pages;

use App\Filament\Resources\StockTransfers\Actions\CompleteTransferAction;
use App\Filament\Resources\StockTransfers\StockTransferResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStockTransfer extends ViewRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CompleteTransferAction::make(),
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Filament\Resources\StockAdjustments\Actions\ApplyAdjustmentAction;
use App\Filament\Resources\StockAdjustments\Concerns\ComputesAdjustmentDifferences;
use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStockAdjustment extends EditRecord
{
    use ComputesAdjustmentDifferences;

    protected static string $resource = StockAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ApplyAdjustmentAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->computeDifferences($data);
    }
}

<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Filament\Resources\StockAdjustments\Concerns\ComputesAdjustmentDifferences;
use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockAdjustment extends CreateRecord
{
    use ComputesAdjustmentDifferences;

    protected static string $resource = StockAdjustmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $this->computeDifferences($data);
    }
}

<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesOrders\Concerns\ComputesSalesOrderTotals;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesOrder extends CreateRecord
{
    use ComputesSalesOrderTotals;

    protected static string $resource = SalesOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $this->computeTotals($data);
    }
}

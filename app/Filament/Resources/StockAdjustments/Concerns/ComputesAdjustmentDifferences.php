<?php

namespace App\Filament\Resources\StockAdjustments\Concerns;

trait ComputesAdjustmentDifferences
{
    protected function computeDifferences(array $data): array
    {
        foreach ($data['items'] ?? [] as $key => $item) {
            $data['items'][$key]['difference'] = (int) ($item['quantity_after'] ?? 0) - (int) ($item['quantity_before'] ?? 0);
        }

        return $data;
    }
}

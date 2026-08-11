<?php

namespace App\Filament\Resources\SalesOrders\Concerns;

trait ComputesSalesOrderTotals
{
    protected function computeTotals(array $data): array
    {
        $subtotal = 0;

        foreach ($data['items'] ?? [] as $key => $item) {
            $lineTotal = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $data['items'][$key]['subtotal'] = $lineTotal;
            $subtotal += $lineTotal;
        }

        $tax = (float) ($data['tax_total'] ?? 0);
        $discount = (float) ($data['discount_total'] ?? 0);

        $data['subtotal'] = $subtotal;
        $data['total'] = max(0, $subtotal + $tax - $discount);

        return $data;
    }
}

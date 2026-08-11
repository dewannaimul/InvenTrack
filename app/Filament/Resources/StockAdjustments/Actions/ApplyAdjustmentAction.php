<?php

namespace App\Filament\Resources\StockAdjustments\Actions;

use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ApplyAdjustmentAction
{
    public static function make(): Action
    {
        return Action::make('applyAdjustment')
            ->label('Apply adjustment')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (StockAdjustment $record) => $record->status === StockAdjustment::STATUS_DRAFT)
            ->requiresConfirmation()
            ->modalDescription('This will update stock levels to match the new quantities entered.')
            ->action(function (StockAdjustment $record) {
                DB::transaction(function () use ($record) {
                    $stockService = app(StockService::class);

                    foreach ($record->items as $item) {
                        if ($item->difference === 0) {
                            continue;
                        }

                        $stockService->move(
                            productId: $item->product_id,
                            productVariantId: $item->product_variant_id,
                            warehouseId: $record->warehouse_id,
                            quantityDelta: $item->difference,
                            type: StockMovement::TYPE_ADJUSTMENT,
                            reference: $record,
                            note: "{$record->adjustment_number}: {$record->reason}",
                            userId: auth()->id(),
                            allowNegative: true,
                        );
                    }

                    $record->update(['status' => StockAdjustment::STATUS_APPLIED]);
                });

                Notification::make()->title('Adjustment applied')->success()->send();
            });
    }
}

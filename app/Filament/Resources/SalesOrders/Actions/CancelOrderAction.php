<?php

namespace App\Filament\Resources\SalesOrders\Actions;

use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public static function make(): Action
    {
        return Action::make('cancelOrder')
            ->label('Cancel order')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (SalesOrder $record) => in_array($record->status, [
                SalesOrder::STATUS_DRAFT,
                SalesOrder::STATUS_CONFIRMED,
                SalesOrder::STATUS_SHIPPED,
            ]))
            ->requiresConfirmation()
            ->modalDescription('If stock was already deducted, it will be returned to inventory.')
            ->action(function (SalesOrder $record) {
                DB::transaction(function () use ($record) {
                    $stockWasDeducted = in_array($record->status, [
                        SalesOrder::STATUS_CONFIRMED,
                        SalesOrder::STATUS_SHIPPED,
                    ]);

                    if ($stockWasDeducted) {
                        $stockService = app(StockService::class);

                        foreach ($record->items as $item) {
                            $stockService->move(
                                productId: $item->product_id,
                                productVariantId: $item->product_variant_id,
                                warehouseId: $record->warehouse_id,
                                quantityDelta: $item->quantity,
                                type: StockMovement::TYPE_RETURN,
                                reference: $record,
                                note: "Cancelled {$record->order_number}",
                                userId: auth()->id(),
                            );
                        }
                    }

                    $record->update(['status' => SalesOrder::STATUS_CANCELLED]);
                });

                Notification::make()->title('Order cancelled')->success()->send();
            });
    }
}

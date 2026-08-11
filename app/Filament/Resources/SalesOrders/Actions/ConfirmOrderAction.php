<?php

namespace App\Filament\Resources\SalesOrders\Actions;

use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConfirmOrderAction
{
    public static function make(): Action
    {
        return Action::make('confirmOrder')
            ->label('Confirm order')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (SalesOrder $record) => $record->status === SalesOrder::STATUS_DRAFT)
            ->requiresConfirmation()
            ->modalDescription('This will deduct the ordered quantities from stock.')
            ->action(function (SalesOrder $record) {
                try {
                    DB::transaction(function () use ($record) {
                        $stockService = app(StockService::class);

                        foreach ($record->items as $item) {
                            $stockService->move(
                                productId: $item->product_id,
                                productVariantId: $item->product_variant_id,
                                warehouseId: $record->warehouse_id,
                                quantityDelta: -$item->quantity,
                                type: StockMovement::TYPE_SALE,
                                reference: $record,
                                note: "Sold via {$record->order_number}",
                                userId: auth()->id(),
                            );
                        }

                        $record->update(['status' => SalesOrder::STATUS_CONFIRMED]);
                    });

                    Notification::make()->title('Order confirmed and stock deducted')->success()->send();
                } catch (RuntimeException $exception) {
                    Notification::make()
                        ->title('Unable to confirm order')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}

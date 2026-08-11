<?php

namespace App\Filament\Resources\StockTransfers\Actions;

use App\Models\StockTransfer;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompleteTransferAction
{
    public static function make(): Action
    {
        return Action::make('completeTransfer')
            ->label('Complete transfer')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->visible(fn (StockTransfer $record) => in_array($record->status, [
                StockTransfer::STATUS_PENDING,
                StockTransfer::STATUS_IN_TRANSIT,
            ]))
            ->requiresConfirmation()
            ->modalDescription('This will move stock out of the source warehouse and into the destination warehouse.')
            ->action(function (StockTransfer $record) {
                try {
                    DB::transaction(function () use ($record) {
                        $stockService = app(StockService::class);

                        foreach ($record->items as $item) {
                            $stockService->transfer(
                                productId: $item->product_id,
                                productVariantId: $item->product_variant_id,
                                fromWarehouseId: $record->from_warehouse_id,
                                toWarehouseId: $record->to_warehouse_id,
                                quantity: $item->quantity,
                                reference: $record,
                                userId: auth()->id(),
                            );
                        }

                        $record->update(['status' => StockTransfer::STATUS_COMPLETED]);
                    });

                    Notification::make()->title('Transfer completed')->success()->send();
                } catch (RuntimeException $exception) {
                    Notification::make()
                        ->title('Unable to complete transfer')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}

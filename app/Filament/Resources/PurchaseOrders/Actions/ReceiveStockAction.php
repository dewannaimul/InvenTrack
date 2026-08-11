<?php

namespace App\Filament\Resources\PurchaseOrders\Actions;

use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ReceiveStockAction
{
    public static function make(): Action
    {
        return Action::make('receiveStock')
            ->label('Receive stock')
            ->icon('heroicon-o-inbox-arrow-down')
            ->color('success')
            ->visible(fn (PurchaseOrder $record) => in_array($record->status, [
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ]))
            ->modalHeading(fn (PurchaseOrder $record) => "Receive stock against {$record->po_number}")
            ->modalDescription('Enter the quantity that actually arrived for each item. Partial receipts are supported — you can receive the rest later.')
            ->modalSubmitActionLabel('Record receipt')
            ->schema(fn (PurchaseOrder $record) => [
                Repeater::make('items')
                    ->label('Items to receive')
                    ->schema([
                        TextInput::make('product_name')
                            ->label('Product')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('remaining')
                            ->label('Remaining')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('receive_quantity')
                            ->label('Receive now')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('purchase_order_item_id')
                            ->hidden(),
                    ])
                    ->columns(4)
                    ->deletable(false)
                    ->addable(false)
                    ->default(
                        $record->items()->with('product')->get()
                            ->filter(fn ($item) => $item->remaining_quantity > 0)
                            ->map(fn ($item) => [
                                'purchase_order_item_id' => $item->id,
                                'product_name' => "{$item->product->name} ({$item->product->sku})",
                                'remaining' => $item->remaining_quantity,
                                'receive_quantity' => $item->remaining_quantity,
                            ])
                            ->values()
                            ->all()
                    ),
            ])
            ->action(function (PurchaseOrder $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $stockService = app(StockService::class);
                    $receivedAny = false;

                    foreach ($data['items'] as $row) {
                        $qty = (int) ($row['receive_quantity'] ?? 0);

                        if ($qty <= 0) {
                            continue;
                        }

                        $item = $record->items()->find($row['purchase_order_item_id']);

                        if (! $item || $qty > $item->remaining_quantity) {
                            continue;
                        }

                        $stockService->move(
                            productId: $item->product_id,
                            productVariantId: $item->product_variant_id,
                            warehouseId: $record->warehouse_id,
                            quantityDelta: $qty,
                            type: StockMovement::TYPE_PURCHASE,
                            reference: $record,
                            note: "Received against {$record->po_number}",
                            userId: auth()->id(),
                        );

                        $item->increment('received_quantity', $qty);
                        $receivedAny = true;
                    }

                    if ($receivedAny) {
                        $record->refresh();
                        $allReceived = $record->items->every(fn ($item) => $item->received_quantity >= $item->quantity);
                        $record->update([
                            'status' => $allReceived ? PurchaseOrder::STATUS_RECEIVED : PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                        ]);
                    }
                });

                Notification::make()->title('Stock received')->success()->send();
            })
            ->modalWidth('2xl');
    }
}

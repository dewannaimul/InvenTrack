<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    /**
     * Apply a signed quantity change to a product's stock in a warehouse and
     * record the movement for audit purposes. Runs inside a locking
     * transaction so concurrent updates cannot corrupt the balance.
     */
    public function move(
        int $productId,
        ?int $productVariantId,
        int $warehouseId,
        int $quantityDelta,
        string $type,
        ?Model $reference = null,
        ?string $note = null,
        ?int $userId = null,
        bool $allowNegative = false,
    ): Stock {
        return DB::transaction(function () use (
            $productId, $productVariantId, $warehouseId, $quantityDelta,
            $type, $reference, $note, $userId, $allowNegative
        ) {
            $stock = Stock::query()
                ->where('product_id', $productId)
                ->where('product_variant_id', $productVariantId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = Stock::create([
                    'product_id' => $productId,
                    'product_variant_id' => $productVariantId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $newQuantity = $stock->quantity + $quantityDelta;

            if (! $allowNegative && $newQuantity < 0) {
                throw new RuntimeException(
                    "Insufficient stock for product #{$productId} in warehouse #{$warehouseId}: ".
                    "available {$stock->quantity}, requested ".abs($quantityDelta).'.'
                );
            }

            $stock->quantity = $newQuantity;
            $stock->save();

            StockMovement::create([
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'quantity' => $quantityDelta,
                'quantity_after' => $newQuantity,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'note' => $note,
                'user_id' => $userId,
            ]);

            return $stock->fresh();
        });
    }

    public function quantityOnHand(int $productId, ?int $productVariantId, int $warehouseId): int
    {
        return (int) Stock::query()
            ->where('product_id', $productId)
            ->where('product_variant_id', $productVariantId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    public function transfer(
        int $productId,
        ?int $productVariantId,
        int $fromWarehouseId,
        int $toWarehouseId,
        int $quantity,
        ?Model $reference = null,
        ?int $userId = null,
    ): void {
        DB::transaction(function () use ($productId, $productVariantId, $fromWarehouseId, $toWarehouseId, $quantity, $reference, $userId) {
            $this->move(
                productId: $productId,
                productVariantId: $productVariantId,
                warehouseId: $fromWarehouseId,
                quantityDelta: -$quantity,
                type: StockMovement::TYPE_TRANSFER_OUT,
                reference: $reference,
                note: 'Stock transfer out',
                userId: $userId,
            );

            $this->move(
                productId: $productId,
                productVariantId: $productVariantId,
                warehouseId: $toWarehouseId,
                quantityDelta: $quantity,
                type: StockMovement::TYPE_TRANSFER_IN,
                reference: $reference,
                note: 'Stock transfer in',
                userId: $userId,
            );
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $stockService = app(StockService::class);
        $admin = User::where('email', 'admin@inventrack.test')->first();
        $mainWarehouse = Warehouse::where('code', 'MAIN')->first();
        $secondaryWarehouse = Warehouse::where('code', 'SEC')->first();
        $products = Product::where('has_variants', false)->get();
        $suppliers = Supplier::all();
        $customers = Customer::all();

        // --- Purchase orders ---
        foreach (range(1, 8) as $i) {
            $supplier = $suppliers->random();
            $items = $products->random(fake()->numberBetween(2, 4));
            $status = fake()->randomElement([
                PurchaseOrder::STATUS_RECEIVED,
                PurchaseOrder::STATUS_RECEIVED,
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            ]);

            $po = PurchaseOrder::create([
                'po_number' => 'PO-'.now()->subDays(60 - $i)->format('Ymd').'-'.strtoupper(Str::random(4)),
                'supplier_id' => $supplier->id,
                'warehouse_id' => $mainWarehouse->id,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'order_date' => now()->subDays(fake()->numberBetween(5, 60)),
                'expected_date' => now()->subDays(fake()->numberBetween(0, 4)),
                'created_by' => $admin?->id,
            ]);

            $subtotal = 0;

            foreach ($items as $product) {
                $qty = fake()->numberBetween(10, 50);
                $unitCost = $product->cost_price;
                $lineTotal = $qty * $unitCost;
                $subtotal += $lineTotal;

                $receivedQty = match ($status) {
                    PurchaseOrder::STATUS_RECEIVED => $qty,
                    PurchaseOrder::STATUS_PARTIALLY_RECEIVED => intdiv($qty, 2),
                    default => 0,
                };

                $item = $po->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'received_quantity' => $receivedQty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $lineTotal,
                ]);

                if ($receivedQty > 0) {
                    $stockService->move(
                        productId: $product->id,
                        productVariantId: null,
                        warehouseId: $mainWarehouse->id,
                        quantityDelta: $receivedQty,
                        type: StockMovement::TYPE_PURCHASE,
                        reference: $po,
                        note: "Received against {$po->po_number}",
                        userId: $admin?->id,
                    );
                }
            }

            $po->update([
                'status' => $status,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'amount_paid' => $status === PurchaseOrder::STATUS_RECEIVED ? $subtotal : 0,
                'payment_status' => $status === PurchaseOrder::STATUS_RECEIVED ? 'paid' : 'unpaid',
            ]);

            if ($status === PurchaseOrder::STATUS_RECEIVED) {
                $po->payments()->create([
                    'amount' => $subtotal,
                    'method' => 'bank_transfer',
                    'paid_on' => $po->order_date,
                    'created_by' => $admin?->id,
                ]);
            }
        }

        // --- Sales orders (spread over the last 30 days for the dashboard chart) ---
        foreach (range(1, 20) as $i) {
            $customer = $customers->random();
            $items = $products->random(fake()->numberBetween(1, 3));
            $status = fake()->randomElement([
                SalesOrder::STATUS_COMPLETED,
                SalesOrder::STATUS_COMPLETED,
                SalesOrder::STATUS_CONFIRMED,
                SalesOrder::STATUS_SHIPPED,
                SalesOrder::STATUS_DRAFT,
            ]);
            $orderDate = now()->subDays(fake()->numberBetween(0, 29));

            $so = SalesOrder::create([
                'order_number' => 'SO-'.$orderDate->format('Ymd').'-'.strtoupper(Str::random(4)),
                'customer_id' => $customer->id,
                'warehouse_id' => $mainWarehouse->id,
                'status' => SalesOrder::STATUS_DRAFT,
                'order_date' => $orderDate,
                'created_by' => $admin?->id,
            ]);

            $subtotal = 0;

            foreach ($items as $product) {
                $qty = fake()->numberBetween(1, 8);
                $unitPrice = $product->selling_price;
                $lineTotal = $qty * $unitPrice;
                $subtotal += $lineTotal;

                $so->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                ]);

                if (in_array($status, [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_COMPLETED])) {
                    $available = $stockService->quantityOnHand($product->id, null, $mainWarehouse->id);
                    $deduction = min($qty, $available);

                    if ($deduction > 0) {
                        $stockService->move(
                            productId: $product->id,
                            productVariantId: null,
                            warehouseId: $mainWarehouse->id,
                            quantityDelta: -$deduction,
                            type: StockMovement::TYPE_SALE,
                            reference: $so,
                            note: "Sold via {$so->order_number}",
                            userId: $admin?->id,
                        );
                    }
                }
            }

            $isPaid = $status === SalesOrder::STATUS_COMPLETED;

            $so->update([
                'status' => $status,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'amount_paid' => $isPaid ? $subtotal : 0,
                'payment_status' => $isPaid ? 'paid' : 'unpaid',
            ]);

            if ($isPaid) {
                $so->payments()->create([
                    'amount' => $subtotal,
                    'method' => fake()->randomElement(['cash', 'card', 'bank_transfer']),
                    'paid_on' => $orderDate,
                    'created_by' => $admin?->id,
                ]);
            }
        }

        // --- A completed stock transfer ---
        $transfer = StockTransfer::create([
            'transfer_number' => 'TR-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'from_warehouse_id' => $mainWarehouse->id,
            'to_warehouse_id' => $secondaryWarehouse->id,
            'status' => StockTransfer::STATUS_PENDING,
            'transfer_date' => now()->subDays(3),
            'created_by' => $admin?->id,
        ]);

        foreach ($products->random(3) as $product) {
            $qty = fake()->numberBetween(5, 15);
            $transfer->items()->create(['product_id' => $product->id, 'quantity' => $qty]);

            $stockService->transfer(
                productId: $product->id,
                productVariantId: null,
                fromWarehouseId: $mainWarehouse->id,
                toWarehouseId: $secondaryWarehouse->id,
                quantity: $qty,
                reference: $transfer,
                userId: $admin?->id,
            );
        }

        $transfer->update(['status' => StockTransfer::STATUS_COMPLETED]);

        // --- An applied stock adjustment (stock count correction) ---
        $adjustment = StockAdjustment::create([
            'adjustment_number' => 'ADJ-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'warehouse_id' => $mainWarehouse->id,
            'reason' => 'correction',
            'status' => StockAdjustment::STATUS_DRAFT,
            'notes' => 'Physical stock count correction',
            'created_by' => $admin?->id,
        ]);

        foreach ($products->random(2) as $product) {
            $before = $stockService->quantityOnHand($product->id, null, $mainWarehouse->id);
            $after = max(0, $before - fake()->numberBetween(1, 5));

            $adjustment->items()->create([
                'product_id' => $product->id,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'difference' => $after - $before,
            ]);

            $stockService->move(
                productId: $product->id,
                productVariantId: null,
                warehouseId: $mainWarehouse->id,
                quantityDelta: $after - $before,
                type: StockMovement::TYPE_ADJUSTMENT,
                reference: $adjustment,
                note: "{$adjustment->adjustment_number}: correction",
                userId: $admin?->id,
                allowNegative: true,
            );
        }

        $adjustment->update(['status' => StockAdjustment::STATUS_APPLIED]);
    }
}

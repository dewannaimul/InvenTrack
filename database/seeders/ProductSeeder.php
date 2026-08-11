<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    protected array $catalog = [
        'Smartphones' => ['Galaxy S Series Phone', 'Pixel Phone', 'iPhone Clone X', 'Budget Android Phone'],
        'Laptops' => ['UltraBook Pro 14"', 'Gaming Laptop 17"', 'Chromebook Lite', 'Business Laptop 15"'],
        'Accessories' => ['USB-C Charger', 'Wireless Mouse', 'Bluetooth Headphones', 'Laptop Sleeve', 'Phone Case'],
        'Beverages' => ['Sparkling Water 500ml', 'Orange Juice 1L', 'Cola 330ml Can', 'Instant Coffee Jar'],
        'Snacks' => ['Potato Chips 150g', 'Chocolate Bar', 'Mixed Nuts 200g', 'Granola Bar Box'],
        'Men' => ['Men Cotton T-Shirt', 'Men Denim Jeans', 'Men Running Shoes'],
        'Women' => ['Women Summer Dress', 'Women Yoga Pants', 'Women Sneakers'],
        'Cookware' => ['Non-Stick Frying Pan', 'Stainless Steel Pot Set', 'Cutting Board'],
        'Furniture' => ['Office Chair', 'Study Desk', 'Bookshelf'],
    ];

    public function run(): void
    {
        $units = Unit::all()->keyBy('name');
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        $mainWarehouse = $warehouses->firstWhere('code', 'MAIN');
        $secondaryWarehouse = $warehouses->firstWhere('code', 'SEC');
        $stockService = app(StockService::class);

        $index = 0;

        foreach ($this->catalog as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->first();

            foreach ($products as $productName) {
                $index++;
                $brand = $brands->random();
                $unit = $units->get('Pieces');
                $costPrice = fake()->randomFloat(2, 5, 300);
                $sellingPrice = round($costPrice * fake()->randomFloat(2, 1.2, 1.8), 2);
                $reorderLevel = fake()->numberBetween(10, 30);

                $product = Product::create([
                    'sku' => 'SKU-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                    'barcode' => fake()->unique()->ean13(),
                    'name' => $productName,
                    'slug' => Str::slug($productName).'-'.$index,
                    'description' => fake()->sentence(12),
                    'category_id' => $category?->id,
                    'brand_id' => $brand->id,
                    'unit_id' => $unit->id,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'tax_rate' => fake()->randomElement([0, 5, 10]),
                    'reorder_level' => $reorderLevel,
                    'has_variants' => false,
                    'track_stock' => true,
                    'is_active' => true,
                ]);

                // Roughly 1 in 6 products start intentionally low on stock to populate alerts.
                $isLowStock = $index % 6 === 0;
                $mainQty = $isLowStock ? fake()->numberBetween(0, $reorderLevel) : fake()->numberBetween($reorderLevel + 10, $reorderLevel + 150);
                $secondaryQty = fake()->numberBetween(0, 60);

                $stockService->move(
                    productId: $product->id,
                    productVariantId: null,
                    warehouseId: $mainWarehouse->id,
                    quantityDelta: $mainQty,
                    type: StockMovement::TYPE_ADJUSTMENT,
                    note: 'Opening stock',
                );

                if ($secondaryQty > 0) {
                    $stockService->move(
                        productId: $product->id,
                        productVariantId: null,
                        warehouseId: $secondaryWarehouse->id,
                        quantityDelta: $secondaryQty,
                        type: StockMovement::TYPE_ADJUSTMENT,
                        note: 'Opening stock',
                    );
                }
            }
        }

        // Add variant support to a couple of apparel products.
        Product::whereIn('name', ['Men Cotton T-Shirt', 'Women Summer Dress'])->get()->each(function (Product $product) use ($mainWarehouse, $stockService) {
            $product->update(['has_variants' => true]);

            foreach (['Small', 'Medium', 'Large'] as $size) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $product->sku.'-'.strtoupper(substr($size, 0, 1)),
                    'variant_attributes' => ['Size' => $size],
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'is_active' => true,
                ]);

                $stockService->move(
                    productId: $product->id,
                    productVariantId: $variant->id,
                    warehouseId: $mainWarehouse->id,
                    quantityDelta: fake()->numberBetween(10, 50),
                    type: StockMovement::TYPE_ADJUSTMENT,
                    note: 'Opening stock',
                );
            }
        });
    }
}

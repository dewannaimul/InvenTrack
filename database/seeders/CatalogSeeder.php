<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Pieces' => 'pcs', 'Kilogram' => 'kg', 'Box' => 'box', 'Liter' => 'ltr'] as $name => $symbol) {
            Unit::firstOrCreate(['name' => $name], ['symbol' => $symbol]);
        }

        $categories = [
            'Electronics' => ['Smartphones', 'Laptops', 'Accessories'],
            'Groceries' => ['Beverages', 'Snacks'],
            'Apparel' => ['Men', 'Women'],
            'Home & Kitchen' => ['Cookware', 'Furniture'],
        ];

        foreach ($categories as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName, 'is_active' => true],
            );

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($parentName.'-'.$childName)],
                    ['name' => $childName, 'parent_id' => $parent->id, 'is_active' => true],
                );
            }
        }

        foreach (['Acme', 'Globex', 'Initech', 'Umbrella', 'Stark Industries', 'Wayne Enterprises'] as $brandName) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($brandName)],
                ['name' => $brandName, 'is_active' => true],
            );
        }

        Warehouse::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Warehouse', 'address' => '100 Commerce St, Springfield', 'is_default' => true, 'is_active' => true],
        );

        Warehouse::firstOrCreate(
            ['code' => 'SEC'],
            ['name' => 'Secondary Warehouse', 'address' => '55 Industrial Ave, Shelbyville', 'is_default' => false, 'is_active' => true],
        );
    }
}

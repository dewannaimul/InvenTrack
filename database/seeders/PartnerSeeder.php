<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Global Supply Co.', 'Northwind Traders', 'Contoso Distribution', 'Fabrikam Wholesale', 'Tailwind Imports'] as $name) {
            Supplier::firstOrCreate(
                ['name' => $name],
                [
                    'company' => $name,
                    'email' => 'sales@'.Str::slug($name).'.test',
                    'phone' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'tax_number' => fake()->numerify('TAX-#######'),
                    'is_active' => true,
                ]
            );
        }

        for ($i = 0; $i < 12; $i++) {
            Customer::create([
                'name' => fake()->name(),
                'company' => fake()->boolean(40) ? fake()->company() : null,
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'customer_type' => fake()->randomElement(['retail', 'retail', 'wholesale']),
                'is_active' => true,
            ]);
        }
    }
}

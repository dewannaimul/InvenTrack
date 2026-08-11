<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndUsersSeeder::class,
            CatalogSeeder::class,
            ProductSeeder::class,
            PartnerSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}

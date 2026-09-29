<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the storefront with a working catalog.
     *
     * NOTE: deliberately no WithoutModelEvents trait. Category derives its
     * `path` column in a saving hook, and Cart mints its token in a creating
     * hook -- muting model events here would leave categories with empty paths
     * and every storefront URL unresolvable.
     *
     * Order matters: options and categories must exist before products can
     * reference them.
     */
    public function run(): void
    {
        $this->command?->newLine();
        $this->command?->info('Seeding storefront');

        $this->call([
            UserSeeder::class,
            OptionSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ShippingMethodSeeder::class,
            StoreSeeder::class,
            PageSeeder::class,
            ContentBlockSeeder::class,
        ]);

        $this->command?->newLine();
    }
}

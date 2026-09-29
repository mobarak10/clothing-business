<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Standard Delivery',
                'description' => 'Delivered by our courier partner across Bangladesh.',
                'price' => 12_000,       // ৳120
                'free_over' => 500_000,  // free above ৳5,000
                'estimated_days_min' => 3,
                'estimated_days_max' => 5,
                'position' => 0,
            ],
            [
                'name' => 'Express Delivery',
                'description' => 'Next business day inside Dhaka, two days elsewhere.',
                'price' => 30_000,       // ৳300
                'free_over' => null,
                'estimated_days_min' => 1,
                'estimated_days_max' => 2,
                'position' => 1,
            ],
            [
                'name' => 'Boutique Collection',
                'description' => 'Reserve online and collect from a boutique of your choice.',
                'price' => 0,
                'free_over' => null,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'position' => 2,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::updateOrCreate(
                ['name' => $method['name']],
                $method + ['is_active' => true],
            );
        }

        $this->command?->info('  Shipping: '.count($methods).' methods');
    }
}

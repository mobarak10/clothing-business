<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        // Prices are minor units. 20k-250k poisha = ৳200-৳2,500... realistic
        // luxury pricing sits far higher, so seed 15,000-250,000 taka.
        $price = fake()->numberBetween(15_000, 250_000) * 100;

        return [
            'name' => $name,
            'sku_root' => strtoupper(Str::random(4)).fake()->unique()->numerify('######'),
            'subtitle' => fake()->sentence(4),
            'description' => fake()->paragraph(4),
            'composition' => fake()->randomElement([
                '100% Virgin Wool', '100% Cotton', '70% Wool, 30% Cashmere',
                '100% Silk', '100% Calfskin', '80% Cotton, 20% Linen',
            ]),
            'care_instructions' => 'Dry clean only. Do not bleach.',
            'fit' => fake()->randomElement(['Slim Fit', 'Regular Fit', 'Relaxed Fit']),
            'base_price' => $price,
            'compare_at_price' => null,
            'is_active' => true,
            'is_new_arrival' => false,
            'published_at' => now()->subDays(fake()->numberBetween(1, 200)),
        ];
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            // Marked down 30% from the compare-at figure.
            'compare_at_price' => (int) round($attributes['base_price'] / 0.7),
        ]);
    }

    public function newArrival(): static
    {
        return $this->state(fn () => [
            'is_new_arrival' => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 21)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
            'published_at' => null,
        ]);
    }
}

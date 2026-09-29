<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(Str::random(6)).fake()->unique()->numerify('####'),

            // Null inherits the product's base price -- the common case.
            'price' => null,
            'compare_at_price' => null,
            'stock_quantity' => fake()->numberBetween(0, 25),
            'weight_grams' => fake()->numberBetween(200, 2000),
            'position' => 0,
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 0]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 2]);
    }

    public function inStock(int $quantity = 10): static
    {
        return $this->state(fn () => ['stock_quantity' => $quantity]);
    }
}

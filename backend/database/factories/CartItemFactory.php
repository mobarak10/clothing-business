<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => fake()->numberBetween(1, 3),
            'unit_price' => fake()->numberBetween(15_000, 250_000) * 100,
        ];
    }

    /** Snapshots the variant's real price, so the line is not stale. */
    public function forVariant(ProductVariant $variant, int $quantity = 1): static
    {
        return $this->state(fn () => [
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => $variant->price()->minor,
        ]);
    }
}

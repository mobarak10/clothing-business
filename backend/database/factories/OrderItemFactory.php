<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(15_000, 250_000) * 100;
        $quantity = fake()->numberBetween(1, 3);
        $name = Str::title(fake()->words(3, true));

        return [
            'order_id' => Order::factory(),
            'product_variant_id' => null,
            'product_name' => $name,
            'product_slug' => Str::slug($name),
            'sku' => strtoupper(Str::random(6)),
            'options' => ['Color' => 'Navy', 'Size' => '50'],
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
        ];
    }

    /** Freezes a real variant onto the line, the way checkout does. */
    public function fromVariant(ProductVariant $variant, int $quantity = 1): static
    {
        $unitPrice = $variant->price()->minor;

        return $this->state(fn () => [
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'product_slug' => $variant->product->slug,
            'sku' => $variant->sku,
            'options' => $variant->optionMap(),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20_000, 400_000) * 100;
        $shipping = 15_000;

        $address = [
            'first_name' => fake()->firstName('male'),
            'last_name' => fake()->lastName(),
            'company' => null,
            'phone' => '017'.fake()->numerify('########'),
            'line1' => fake()->buildingNumber().' '.fake()->streetName(),
            'line2' => null,
            'city' => 'Dhaka',
            'district' => 'Gulshan',
            'postcode' => fake()->numerify('####'),
            'country_code' => 'BD',
        ];

        return [
            'order_number' => 'ATL-'.now()->year.'-'.fake()->unique()->numerify('######'),
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'phone' => $address['phone'],
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'currency' => config('shop.currency.code'),
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'shipping_total' => $shipping,
            'tax_total' => 0,
            'grand_total' => $subtotal + $shipping,
            'coupon_code' => null,
            'shipping_method_name' => 'Standard Delivery',
            'shipping_address' => $address,
            'billing_address' => $address,
            'placed_at' => now(),
        ];
    }

    public function guest(): static
    {
        return $this->state(fn () => ['user_id' => null]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::Paid,
            'status' => OrderStatus::Processing,
        ]);
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}

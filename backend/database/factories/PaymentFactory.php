<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway' => 'sslcommerz',
            'gateway_transaction_id' => (string) Str::uuid(),
            'val_id' => null,
            'amount' => fake()->numberBetween(20_000, 400_000) * 100,
            'currency' => config('shop.currency.code'),
            'status' => PaymentStatus::Pending,
            'card_type' => null,
            'raw_payload' => null,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Paid,
            'val_id' => fake()->numerify('##########'),
            'card_type' => fake()->randomElement(['VISA-Dutch Bangla', 'MASTER-Brac Bank', 'BKASH-BKash']),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => PaymentStatus::Failed]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'token' => (string) Str::uuid(),
            'currency' => config('shop.currency.code'),
            'expires_at' => now()->addDays(30),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn () => ['user_id' => $user?->id ?? User::factory()]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }
}

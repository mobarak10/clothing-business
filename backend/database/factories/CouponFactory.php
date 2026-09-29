<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'description' => fake()->sentence(6),
            'type' => 'percent',
            // Basis points: 1000 = 10.00%
            'value' => 1000,
            'minimum_spend' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    public function percent(int $basisPoints): static
    {
        return $this->state(fn () => ['type' => 'percent', 'value' => $basisPoints]);
    }

    /** @param int $minorUnits amount off, in poisha */
    public function fixed(int $minorUnits): static
    {
        return $this->state(fn () => ['type' => 'fixed', 'value' => $minorUnits]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function exhausted(): static
    {
        return $this->state(fn () => ['usage_limit' => 5, 'used_count' => 5]);
    }
}

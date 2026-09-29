<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    public function definition(): array
    {
        return [
            'name' => 'Standard Delivery',
            'description' => 'Delivered by our courier partner.',
            'price' => 15_000,
            'free_over' => null,
            'estimated_days_min' => 3,
            'estimated_days_max' => 5,
            'is_active' => true,
            'position' => 0,
        ];
    }

    public function freeOver(int $minorUnits): static
    {
        return $this->state(fn () => ['free_over' => $minorUnits]);
    }
}

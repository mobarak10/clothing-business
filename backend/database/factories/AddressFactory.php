<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'shipping',
            'first_name' => fake()->firstName('male'),
            'last_name' => fake()->lastName(),
            'company' => null,
            'phone' => '017'.fake()->numerify('########'),
            'line1' => fake()->buildingNumber().' '.fake()->streetName(),
            'line2' => null,
            'city' => fake()->randomElement(['Dhaka', 'Chattogram', 'Sylhet', 'Khulna', 'Rajshahi']),
            'district' => fake()->randomElement(['Gulshan', 'Banani', 'Dhanmondi', 'Uttara']),
            'postcode' => fake()->numerify('####'),
            'country_code' => 'BD',
            'is_default' => false,
        ];
    }

    public function billing(): static
    {
        return $this->state(fn () => ['type' => 'billing']);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        $city = fake()->randomElement(['Dhaka', 'Chattogram', 'Sylhet']);
        $name = $city.' '.fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'line1' => fake()->buildingNumber().' '.fake()->streetName(),
            'city' => $city,
            'district' => fake()->randomElement(['Gulshan', 'Banani', 'Dhanmondi']),
            'postcode' => fake()->numerify('####'),
            'country_code' => 'BD',
            'phone' => '+8809'.fake()->numerify('########'),
            'latitude' => fake()->latitude(20.5, 26.5),
            'longitude' => fake()->longitude(88.0, 92.5),
            'opening_hours' => [
                'mon' => ['10:00', '20:00'],
                'tue' => ['10:00', '20:00'],
                'wed' => ['10:00', '20:00'],
                'thu' => ['10:00', '20:00'],
                'fri' => ['15:00', '20:00'],
                'sat' => ['10:00', '20:00'],
                'sun' => ['10:00', '20:00'],
            ],
            'offers_made_to_measure' => fake()->boolean(40),
            'is_active' => true,
            'position' => 0,
        ];
    }
}

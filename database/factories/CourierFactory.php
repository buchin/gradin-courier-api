<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'vehicle_type' => fake()->randomElement(['motorcycle', 'car', 'van', 'bicycle']),
            'license_plate' => fake()->unique()->regexify('[A-Z]{1,2} [0-9]{1,4} [A-Z]{1,3}'),
            'level' => fake()->numberBetween(1, 5),
            'is_active' => fake()->boolean(90),
            'registered_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seat>
 */
class SeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seat_no' => fake()->unique()->numberBetween($min = 1, $max = 9000),
            'note' => fake()->sentence(),
            'status' => fake()->randomElement([1, 0]),
        ];
    }
}

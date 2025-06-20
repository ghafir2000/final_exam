<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class VeterinarianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'degree' => $this->faker->randomElement(['BSc', 'MSc', 'PhD']),
            'degree_year' => $this->faker->year,
            'university' => $this->faker->company(),
        ];
    }
}

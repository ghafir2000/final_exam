<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
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
             'email' => fake()->unique()->safeEmail(),
             'email_verified_at' => now(),
             'phone' => fake()->phoneNumber(),
             'password' => Hash::make('password'),
             'remember_token' => Str::random(10),
             'country' => fake()->countryCode(),
             'address' => fake()->address(),
             // Default these to null, but ensure they're required through custom handling
             'userable_type' => null,
             'userable_id' => null,
         ];
     }
     
     public function ensureUserable(string $modelClass): self
     {
         // Validate that a valid model class is provided
         if (empty($modelClass)) {
             throw new \InvalidArgumentException('userable_type must be explicitly provided.');
         }
         if ($modelClass === Admin::class) {
            throw new \InvalidArgumentException('Admin cannot be used as a userable_type for this factory.');
        }  
         return $this->state(function () use ($modelClass) {
             $userable = $modelClass::factory()->create();
     
             return [
                 'userable_type' => $modelClass,
                 'userable_id' => $userable->id,
             ];
         });
     }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

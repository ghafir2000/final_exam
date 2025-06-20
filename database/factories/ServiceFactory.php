<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Admin;
use App\Models\Breed;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
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
            'description' => fake()->text(),
            'price' => fake()->randomFloat(2, 100.0, 1000.0), 
            'duration' => fake()->randomElement([30, 60, 90]),
            'available_times' => null,
            'servicable_type' => null,
            'servicable_id' => null,
        ];
    }

    public function ensureServicable(string $modelClass): self
    {
        // Validate that a valid model class is provided
        if (empty($modelClass)) {
            throw new \InvalidArgumentException('servicable_type must be explicitly provided.');
        }
        if ($modelClass === Admin::class || $modelClass === Customer::class) {
           throw new \InvalidArgumentException('Admin and customer cannot be used as a servicable_type for this factory.');
       }  
        // Create a servicable user (partner ot vet) instance via the shared userable polymorphic structure, aka new user as a servicable
        return $this->state(function () use ($modelClass) {
            $servicable = User::factory()->ensureUserable($modelClass)->create();
    
            return [    
                'servicable_type' => $modelClass,
                'servicable_id' => $servicable->id,
            ];
        });
    }

}

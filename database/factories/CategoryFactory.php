<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            'category_id' => null,
        ];
    }

    public function withCategory(): self
    {
        return $this->afterCreating(function ($category) {
            if (fake()->boolean(50)) {
                $parentCategory = Category::factory()->create();
                $category->update(['category_id' => $parentCategory->id]);
            }
        });
    }
    
}

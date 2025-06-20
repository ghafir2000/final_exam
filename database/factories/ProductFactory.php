<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
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
            'CRF' => fake()->word(),
            'price' => fake()->randomFloat(2, 100.0, 1000.0), 
        ];
    }

    public function withCategories()
    {
        return $this->afterCreating(function ($product) {
            // Fetch random categories (main or subcategories) and associate them
            $categories = Category::all()->random(2); // Select 2 random categories (main or subcategories)
            
            foreach ($categories as $category) {
                $product->categories()->attach($category->id); // Link product to category
                
                // Ensure the product is also linked to all subcategories of the selected category
                $subcategories = Category::where('category_id', $category->id)->get();
                foreach ($subcategories as $subcategory) {
                    $product->categories()->attach($subcategory->id);
                }
            }
        });
    }
    public function withProductable()
    {
        return $this->afterCreating(function ($product) {
            $user = \App\Models\User::whereHas('userable', function ($query) {
                $query->whereIn('userable_type', [\App\Models\Veterinarian::class, \App\Models\Partner::class]);
            })->inRandomOrder()->first();

            if ($user) {
                $product->update([
                    'productable_id' => $user->userable_id,
                    'productable_type' => $user->userable_type,
                ]);
            }
        });
    }

}

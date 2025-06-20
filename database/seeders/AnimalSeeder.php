<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Animal;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $animals = Animal::factory()->count(10)->create();
        foreach ($animals as $animal) {
            Breed::factory()->count($animal->number_of_breeds)->create(['animal_id' => $animal->id]);
        }
    }
}

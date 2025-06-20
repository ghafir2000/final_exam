<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Breed;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // --- 1. Generate the available times structure ---
        $generatedTimes = [];
        $currentPhpTime = strtotime('10:00');
        $endPhpTime = strtotime('17:00'); // 5 PM

        while ($currentPhpTime < $endPhpTime) {
            $timeKey = date('H:i', $currentPhpTime);
            $generatedTimes[] = $timeKey;

            $currentPhpTime = strtotime("+1 hour", $currentPhpTime);
        }
            
        // If no times were generated (e.g., if start >= end), provide a default empty JSON array
        $availableTimesJson = json_encode($generatedTimes);
        
        $Services = Service::factory()->count(20)->state(fn (array $attributes) => [
            'available_times' => $availableTimesJson,
            'servicable_type' => fake()->randomElement(['App\\Models\\Partner', 'App\\Models\\Veterinarian']),
            'servicable_id' => function (array $attributes) {
                return $attributes['servicable_type']::inRandomOrder()->value('id');
            },
        ])->create();
        foreach($Services as $service){
            $service->breeds()->sync(
                Breed::inRandomOrder()->limit(rand(3, 7))->pluck('id')->toArray()
            );
        }
    }
}

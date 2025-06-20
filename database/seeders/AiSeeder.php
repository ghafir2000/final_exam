<?php

namespace Database\Seeders;

use App\Models\AI;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ai = AI::create([
            'name' => 'Dr.Pet-er',
            'model' => env('GEMINI_MODEL_NAME', 'gemenai'),
            'description' => file_get_contents(public_path('prompts/Dr.Pet-er.txt')),
        ]);
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            
            // CategorySeeder::class,
            // AnimalSeeder::class, //calls breed seeder 
            // UserSeeder::class, //calls customer seeder 
            // ProductSeeder::class,
            // ServiceSeeder::class, 
            // PermissionsSeeder::class,
            // PermissionsSeeder2::class,
            BlogSeeder::class,
            EmbeddingSeeder::class,
            AdminSeeder::class,

        ]);
    }
}

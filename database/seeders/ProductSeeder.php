<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Schema::disableForeignKeyConstraints(); // Disable FK checks temporarily

        // Product::truncate();
        
        for ($i = 0; $i < 30; $i++) {
            Product::factory()
            ->count(mt_rand(1, 5))
            ->withCategories()
            ->withProductable()
            ->create();
        }
        
        // Schema::enableForeignKeyConstraints();
    }
}

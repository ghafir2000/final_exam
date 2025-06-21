<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partner;
use App\Models\Customer;
use App\Models\Veterinarian;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed customers
        User::factory()->count(20)->ensureUserable(Customer::class)->create();
        // User::factory()->count(20)->ensureUserable(Veterinarian::class)->create();
        // User::factory()->count(20)->ensureUserable(Partner::class)->create();
    }
}

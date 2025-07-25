<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::findOrCreate('admin');

        $user = \App\Models\User::factory()->create([
            'name' => 'ahmad al ghafir',
            'email' => 'ahmadghafeer@gmail.com',
            'email_verified_at' => null,
            'phone' => fake()->phoneNumber(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'country' => fake()->countryCode(),
            'address' => fake()->address(),
            'userable_id' => 1,
            'userable_type' => 'App\Models\Admin',
        ]);
        $user->assignRole($admin);
    }
}

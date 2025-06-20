<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a role
        $provider = Role::findOrCreate('provider');

        // Create permissions
        $editPayable = Permission::findOrCreate('edit payable');

        // Assign permissions to roles
        $provider->givePermissionTo($editPayable);

        $users = \App\Models\User::whereIn('userable_type', ['App\\Models\\Partner', 'App\\Models\\Veterinarian'])->get();
        foreach ($users as $user){
            $user->assignRole($provider);
        }    
        
    }
}

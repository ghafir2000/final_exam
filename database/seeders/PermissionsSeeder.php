<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Add this line
use Illuminate\Support\Facades\Schema; // Add this line
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        Schema::withoutForeignKeyConstraints(function () {
            // Truncate (clear) existing data from related tables
            // IMPORTANT: Truncate pivot tables first, then the main tables they reference.
            DB::table('model_has_roles')->truncate();
            DB::table('model_has_permissions')->truncate();
            DB::table('role_has_permissions')->truncate(); // This is the table causing the error

            Permission::truncate(); // Clear all permissions
            Role::truncate();       // Clear all roles
        });

        // The above Schema::withoutForeignKeyConstraints closure re-enables
        // foreign key checks automatically when it finishes.
        // No need for Schema::enableForeignKeyConstraints(); here.

        // Create roles
        $admin = Role::findOrCreate('admin');
        $editor = Role::findOrCreate('editor');

        // Create permissions
        $editUsers = Permission::findOrCreate('edit users');
        $editMedia = Permission::findOrCreate('edit media');

        // Assign permissions to roles
        $admin->givePermissionTo([$editUsers, $editMedia]);
        $editor->givePermissionTo($editMedia);

        // Optional: Assign a default role to the first user if you have one
        // User::find(1)->assignRole('admin'); // Example: requires User model
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a role
        $admin = Role::findOrCreate('admin');
        $editor = Role::findOrCreate('editor');

        // Create permissions
        $editUsers = Permission::findOrCreate('edit users');
        $editMedia = Permission::findOrCreate('edit media');

        // Assign permissions to roles
        $admin->permissions()->sync([$editUsers->id, $editMedia->id]);
        $editor->permissions()->sync([$editMedia->id]);
        
    }
}

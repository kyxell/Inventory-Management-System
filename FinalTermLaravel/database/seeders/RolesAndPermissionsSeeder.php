<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view products',
            'manage products',
            'view inventory',
            'manage inventory',
            'view users',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);
        $manager->syncPermissions(['view products', 'manage products', 'view inventory', 'manage inventory']);
        $userRole->syncPermissions(['view products', 'view inventory']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@inventory.local'],
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'first_name' => 'Administrator',
                'last_name' => '',
                'password' => Hash::make('admin123'),
                'password_hash' => Hash::make('admin123'),
                'role_id' => 1,
                'is_active' => true,
            ]
        );

        $adminUser->assignRole($admin);
        $adminUser->role_id = $admin->id;
        $adminUser->saveQuietly();
    }
}

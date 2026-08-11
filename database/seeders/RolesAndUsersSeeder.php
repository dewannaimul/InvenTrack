<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--no-interaction' => true,
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerPermissions = Permission::query()
            ->where(function ($query) {
                $query->where('name', 'like', 'view%')
                    ->orWhere('name', 'like', 'create%')
                    ->orWhere('name', 'like', 'update%');
            })
            ->where('name', 'not like', '%role%')
            ->where('name', 'not like', '%user%')
            ->get();
        $managerRole->syncPermissions($managerPermissions);

        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staffPermissions = Permission::query()
            ->where('name', 'like', 'view%')
            ->get();
        $staffRole->syncPermissions($staffPermissions);

        $admin = User::firstOrCreate(
            ['email' => 'admin@inventrack.test'],
            [
                'name' => 'Alex Admin',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($superAdminRole);

        $manager = User::firstOrCreate(
            ['email' => 'manager@inventrack.test'],
            [
                'name' => 'Morgan Manager',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole($managerRole);

        $staff = User::firstOrCreate(
            ['email' => 'staff@inventrack.test'],
            [
                'name' => 'Sam Staff',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $staff->assignRole($staffRole);
    }
}

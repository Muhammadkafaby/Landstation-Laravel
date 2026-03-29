<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'code' => Permission::ACCESS_ADMIN,
                'name' => 'Access Admin',
                'module' => 'access',
            ],
            [
                'code' => Permission::ACCESS_POS,
                'name' => 'Access POS',
                'module' => 'access',
            ],
            [
                'code' => Permission::MANAGE_USERS,
                'name' => 'Manage Users',
                'module' => 'users',
            ],
            [
                'code' => Permission::MANAGE_SETTINGS,
                'name' => 'Manage Settings',
                'module' => 'settings',
            ],
            [
                'code' => Permission::MANAGE_MASTER_DATA,
                'name' => 'Manage Master Data',
                'module' => 'master-data',
            ],
            [
                'code' => Permission::MANAGE_BOOKINGS,
                'name' => 'Manage Bookings',
                'module' => 'bookings',
            ],
            [
                'code' => Permission::MANAGE_PAYMENTS,
                'name' => 'Manage Payments',
                'module' => 'payments',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                ],
            );
        }

        $roles = [
            Role::SUPER_ADMIN => 'Super Admin',
            Role::ADMIN => 'Admin',
            Role::CASHIER => 'Cashier',
        ];

        foreach ($roles as $code => $name) {
            Role::query()->updateOrCreate(['code' => $code], ['name' => $name]);
        }

        $permissionIds = Permission::query()
            ->pluck('id', 'code')
            ->all();

        $superAdmin = Role::query()->where('code', Role::SUPER_ADMIN)->firstOrFail();
        $admin = Role::query()->where('code', Role::ADMIN)->firstOrFail();
        $cashier = Role::query()->where('code', Role::CASHIER)->firstOrFail();

        $superAdmin->permissions()->sync(array_values($permissionIds));
        $admin->permissions()->sync([
            $permissionIds[Permission::ACCESS_ADMIN],
            $permissionIds[Permission::MANAGE_SETTINGS],
            $permissionIds[Permission::MANAGE_MASTER_DATA],
            $permissionIds[Permission::MANAGE_BOOKINGS],
            $permissionIds[Permission::MANAGE_PAYMENTS],
        ]);
        $cashier->permissions()->sync([
            $permissionIds[Permission::ACCESS_POS],
            $permissionIds[Permission::MANAGE_BOOKINGS],
            $permissionIds[Permission::MANAGE_PAYMENTS],
        ]);
    }
}

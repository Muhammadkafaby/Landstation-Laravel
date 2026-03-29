<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'code');

        $users = [
            [
                'name' => 'Land Station Super Admin',
                'email' => 'owner@landstation.test',
                'role_id' => $roles[Role::SUPER_ADMIN],
            ],
            [
                'name' => 'Land Station Admin',
                'email' => 'admin@landstation.test',
                'role_id' => $roles[Role::ADMIN],
            ],
            [
                'name' => 'Land Station Cashier',
                'email' => 'cashier@landstation.test',
                'role_id' => $roles[Role::CASHIER],
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $user['role_id'],
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}

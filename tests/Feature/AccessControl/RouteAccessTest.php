<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
});

test('admins can access the dashboard', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();
});

test('cashiers can access the pos', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('pos.index'))
        ->assertOk();
});

test('cashiers can not access the dashboard', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('non staff users can not access internal routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('pos.index'))
        ->assertForbidden();
});

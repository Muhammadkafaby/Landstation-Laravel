<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
});

test('admins can access the dashboard', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard/Index'));
});

test('cashiers can access the pos', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Pos/Dashboard/Index'));
});

test('admins can access management', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('management.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Management/Index'));
});

test('super admins can access management', function () {
    $superAdmin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::SUPER_ADMIN)->value('id'),
    ]);

    $this->actingAs($superAdmin)
        ->get(route('management.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Management/Index'));
});

test('cashiers can not access the dashboard', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('cashiers can not access management', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('management.index'))
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

<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('cashiers are redirected to the pos after login', function () {
    $user = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('pos.index', absolute: false));
});

test('non staff users can not authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->from(route('login'))->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

test('inactive staff users can not authenticate using the login screen', function () {
    $user = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
        'status' => User::STATUS_INACTIVE,
    ]);

    $response = $this->from(route('login'))->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

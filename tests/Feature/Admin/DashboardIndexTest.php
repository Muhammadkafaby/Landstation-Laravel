<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
});

test('dashboard exposes operational summary for admins', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->where('summary.categories', 4)
            ->where('summary.services', 4)
            ->where('summary.timedServices', 3)
            ->where('summary.menuServices', 1)
            ->where('summary.units', 5)
            ->where('summary.bookableUnits', 5)
            ->where('summary.pricingRules', 5)
            ->where('summary.bookingPolicies', 3)
            ->has('categories', 4)
        );
});

test('dashboard exposes category operational cards for super admins', function () {
    $superAdmin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::SUPER_ADMIN)->value('id'),
    ]);

    $this->actingAs($superAdmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->where('categories.2.code', 'playstation')
            ->where('categories.2.featuredService.name', 'PlayStation Regular')
            ->where('categories.2.featuredService.unitsCount', 2)
            ->where('categories.2.featuredService.startingPriceRupiah', 15000)
            ->where('categories.2.featuredService.hasBookingPolicy', true)
        );
});

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

test('management index exposes seeded master data summary for admins', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('management.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Management/Index')
            ->where('summary.categories', 4)
            ->where('summary.services', 4)
            ->where('summary.units', 5)
            ->where('summary.pricingRules', 5)
            ->where('summary.bookingPolicies', 3)
            ->has('categories', 4)
            ->has('categories.0.services')
        );
});

test('management index exposes service-level read model for seeded categories', function () {
    $superAdmin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::SUPER_ADMIN)->value('id'),
    ]);

    $this->actingAs($superAdmin)
        ->get(route('management.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Management/Index')
            ->has('categories', 4)
            ->where('categories.0.code', 'billiard')
            ->where('categories.0.services.0.code', 'billiard-regular')
            ->where('categories.0.services.0.units_count', 2)
            ->where('categories.0.services.0.pricing_rules_count', 1)
            ->where('categories.0.services.0.has_booking_policy', true)
        );
});

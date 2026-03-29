<?php

use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
});

test('public services index exposes seeded service catalog to guests', function () {
    $this->get(route('services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Services/Index')
            ->where('summary.categories', 4)
            ->where('summary.services', 4)
            ->where('summary.units', 5)
            ->has('categories', 4)
            ->where('categories.0.code', 'billiard')
            ->where('categories.0.services.0.slug', 'billiard-regular')
        );
});

test('public services index exposes pricing and booking summary for active services', function () {
    $this->get(route('services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Services/Index')
            ->where('categories.2.code', 'playstation')
            ->where('categories.2.services.0.name', 'PlayStation Regular')
            ->where('categories.2.services.0.unitsCount', 2)
            ->where('categories.2.services.0.hasPricing', true)
            ->where('categories.2.services.0.hasBookingPolicy', true)
            ->where('categories.2.services.0.startingPriceRupiah', 15000)
        );
});

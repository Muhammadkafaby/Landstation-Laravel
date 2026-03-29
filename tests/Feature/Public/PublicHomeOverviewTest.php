<?php

use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
});

test('public homepage exposes seeded overview summary to guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->where('summary.categories', 4)
            ->where('summary.services', 4)
            ->where('summary.units', 5)
            ->has('categories', 4)
        );
});

test('public homepage exposes featured seeded service data', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->where('categories.2.code', 'playstation')
            ->where('categories.2.featuredService.name', 'PlayStation Regular')
            ->where('categories.2.featuredService.unitsCount', 2)
            ->where('categories.2.featuredService.startingPriceRupiah', 15000)
            ->where('categories.2.featuredService.hasBookingPolicy', true)
        );
});

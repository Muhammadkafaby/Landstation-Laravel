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
            ->where('summary.services', 6)
            ->where('summary.units', 43)
            ->has('categories', 4)
        );
});

test('public homepage exposes featured seeded service data', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->where('categories.2.code', 'playstation')
            ->where('categories.2.featuredService.name', 'PS-4')
            ->where('categories.2.featuredService.unitsCount', 4)
            ->where('categories.2.featuredService.startingPriceRupiah', 10000)
            ->where('categories.2.featuredService.hasBookingPolicy', true)
        );
});

<?php

use Inertia\Testing\AssertableInertia as Assert;

test('public homepage renders the module-based public home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Public/Home'));
});

test('public services page renders the module-based service catalog page', function () {
    $this->get(route('services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Public/Services/Index'));
});

<?php

use App\Models\Service;
use App\Models\ServiceBookingPolicy;
use App\Models\ServicePricingRule;
use Database\Seeders\ServiceCatalogSeeder;

test('service catalog seeder creates default pricing rules for timed services', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $playstation = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $billiard = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $rentalRc = Service::query()->where('code', 'rc-adventure')->firstOrFail();

    expect($playstation->pricingRules()->exists())->toBeTrue()
        ->and($billiard->pricingRules()->exists())->toBeTrue()
        ->and($rentalRc->pricingRules()->exists())->toBeTrue();
});

test('service catalog seeder creates booking policies for timed services', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $playstation = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $policy = $playstation->bookingPolicy;

    expect($policy)->toBeInstanceOf(ServiceBookingPolicy::class)
        ->and($policy->service->is($playstation))->toBeTrue()
        ->and($policy->requires_unit_assignment)->toBeTrue()
        ->and($policy->online_booking_allowed)->toBeTrue();
});

test('unit-specific pricing rules can belong to a service unit', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $pricingRule = ServicePricingRule::query()
        ->whereNotNull('service_unit_id')
        ->firstOrFail();

    expect($pricingRule->unit)->not->toBeNull()
        ->and($pricingRule->service)->not->toBeNull();
});

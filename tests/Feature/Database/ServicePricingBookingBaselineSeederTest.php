<?php

use App\Models\Service;
use App\Models\ServiceBookingPolicy;
use App\Models\ServicePricingRule;
use Database\Seeders\ServiceCatalogSeeder;

test('service catalog seeder creates default pricing rules for timed services', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $playstation = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $playstation5 = Service::query()->where('code', 'ps-5')->firstOrFail();
    $simulator = Service::query()->where('code', 'simulator-balap')->firstOrFail();
    $billiard = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $rentalRc = Service::query()->where('code', 'rc-adventure')->firstOrFail();

    expect($playstation->pricingRules()->exists())->toBeTrue()
        ->and($playstation5->pricingRules()->exists())->toBeTrue()
        ->and($simulator->pricingRules()->exists())->toBeTrue()
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

test('timed service pricing rules remain service-wide after service separation', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $serviceWideRuleCount = ServicePricingRule::query()
        ->whereNull('service_unit_id')
        ->count();

    expect($serviceWideRuleCount)->toBe(11);
});

test('timed services expose separate weekday and weekend pricing rules', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $ps4 = Service::query()->where('code', 'ps-regular')->firstOrFail();

    $dayTypes = $ps4->pricingRules()
        ->orderBy('day_type')
        ->pluck('price_per_interval_rupiah', 'day_type')
        ->all();

    expect($dayTypes)->toBe([
        'weekday' => 10000,
        'weekend' => 12000,
    ]);
});

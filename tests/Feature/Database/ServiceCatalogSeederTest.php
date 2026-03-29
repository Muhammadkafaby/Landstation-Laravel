<?php

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\ServiceCatalogSeeder;

test('service catalog seeder creates the baseline land station categories', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $categories = ServiceCategory::query()
        ->orderBy('code')
        ->pluck('name', 'code')
        ->all();

    expect($categories)->toMatchArray([
        'billiard' => 'Billiard',
        'cafe' => 'Cafe',
        'playstation' => 'PlayStation',
        'rental-rc' => 'Rental RC',
    ]);
});

test('service catalog seeder creates services and units through the expected relations', function () {
    $this->seed(ServiceCatalogSeeder::class);

    $playstation = ServiceCategory::query()
        ->where('code', 'playstation')
        ->firstOrFail();

    $service = $playstation->services()
        ->where('code', 'ps-regular')
        ->firstOrFail();

    expect($service)->toBeInstanceOf(Service::class)
        ->and($service->category->is($playstation))->toBeTrue()
        ->and($service->units()->pluck('code')->all())->toMatchArray([
            'ps-01',
            'ps-02',
        ]);
});

<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-02 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('guests can access the public booking create page', function () {
    $this->get(route('bookings.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Bookings/Create')
            ->has('serviceOptions', 3)
        );
});

test('guests can create a valid timed-service booking', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $this->post(route('bookings.store'), [
        'customer_name' => 'Rama Gamer',
        'customer_phone' => '081234567801',
        'customer_email' => 'rama@example.com',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'start_at' => '2026-04-03 14:00:00',
        'end_at' => '2026-04-03 15:00:00',
        'notes' => 'Butuh ruang paling tenang',
    ])
        ->assertRedirect(route('bookings.create'));

    $customer = Customer::query()->where('phone', '081234567801')->firstOrFail();

    $this->assertDatabaseHas('bookings', [
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_PENDING,
        'booking_source' => Booking::SOURCE_PUBLIC,
    ]);
});

test('public booking create rejects windows that violate booking policy', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $this->from(route('bookings.create'))
        ->post(route('bookings.store'), [
            'customer_name' => 'Rama Gamer',
            'customer_phone' => '081234567802',
            'customer_email' => 'rama2@example.com',
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'start_at' => '2026-04-02 10:10:00',
            'end_at' => '2026-04-02 11:10:00',
            'notes' => 'Invalid lead time',
        ])
        ->assertRedirect(route('bookings.create'))
        ->assertSessionHasErrors('start_at');
});

test('public booking create rejects unavailable units', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $staff = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Booking Lama',
        'phone' => '081234567803',
    ]);

    Booking::query()->create([
        'booking_code' => 'BK-BLOCK-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CONFIRMED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-03 14:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 15:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);

    $this->from(route('bookings.create'))
        ->post(route('bookings.store'), [
            'customer_name' => 'Rama Gamer',
            'customer_phone' => '081234567804',
            'customer_email' => 'rama4@example.com',
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'start_at' => '2026-04-03 14:00:00',
            'end_at' => '2026-04-03 15:00:00',
            'notes' => 'Overlap test',
        ])
        ->assertRedirect(route('bookings.create'))
        ->assertSessionHasErrors('service_unit_id');
});

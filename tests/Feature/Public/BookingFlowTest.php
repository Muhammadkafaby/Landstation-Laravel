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
            ->where('serviceOptions.0.layout.mode', 'manual_grid')
            ->where('serviceOptions.0.layout.canvasWidth', 960)
            ->where('serviceOptions.0.units.0.layout.x', 80)
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
        'status' => Booking::STATUS_HELD,
        'booking_source' => Booking::SOURCE_PUBLIC,
    ]);

    $booking = Booking::query()
        ->where('customer_id', $customer->id)
        ->where('service_id', $service->id)
        ->firstOrFail();

    expect($booking->hold_expires_at?->toDateTimeString())->toBe('2026-04-02 10:10:00');
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

test('public booking create rejects units already held by another guest', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $this->post(route('bookings.store'), [
        'customer_name' => 'Hold Pertama',
        'customer_phone' => '081234567890',
        'customer_email' => 'hold1@example.com',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'start_at' => '2026-04-03 14:00:00',
        'end_at' => '2026-04-03 15:00:00',
        'notes' => 'Hold pertama',
    ])->assertRedirect(route('bookings.create'));

    $this->from(route('bookings.create'))
        ->post(route('bookings.store'), [
            'customer_name' => 'Hold Kedua',
            'customer_phone' => '081234567891',
            'customer_email' => 'hold2@example.com',
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'start_at' => '2026-04-03 14:00:00',
            'end_at' => '2026-04-03 15:00:00',
            'notes' => 'Hold kedua',
        ])
        ->assertRedirect(route('bookings.create'))
        ->assertSessionHasErrors('service_unit_id');
});

test('public booking create rejects customers with too many active holds', function () {
    $playstation = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $billiard = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $firstPsUnit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $secondPsUnit = ServiceUnit::query()->where('code', 'ps-02')->firstOrFail();
    $billiardUnit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();

    $payload = [
        'customer_name' => 'Limiter Guest',
        'customer_phone' => '081234567892',
        'customer_email' => 'limit@example.com',
        'notes' => 'Limit test',
    ];

    $this->post(route('bookings.store'), array_merge($payload, [
        'service_id' => $playstation->id,
        'service_unit_id' => $firstPsUnit->id,
        'start_at' => '2026-04-03 14:00:00',
        'end_at' => '2026-04-03 15:00:00',
    ]))->assertRedirect(route('bookings.create'));

    $this->post(route('bookings.store'), array_merge($payload, [
        'service_id' => $playstation->id,
        'service_unit_id' => $secondPsUnit->id,
        'start_at' => '2026-04-03 16:00:00',
        'end_at' => '2026-04-03 17:00:00',
    ]))->assertRedirect(route('bookings.create'));

    $this->from(route('bookings.create'))
        ->post(route('bookings.store'), array_merge($payload, [
            'service_id' => $billiard->id,
            'service_unit_id' => $billiardUnit->id,
            'start_at' => '2026-04-03 18:00:00',
            'end_at' => '2026-04-03 19:00:00',
        ]))
        ->assertRedirect(route('bookings.create'))
        ->assertSessionHasErrors('customer_phone');
});

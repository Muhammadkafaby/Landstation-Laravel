<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-02 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('admins can access the internal booking create page', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('management.bookings.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Bookings/Create')
            ->has('serviceOptions', 3)
        );
});

test('staff with manage bookings can access the internal booking index page', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Daftar Booking',
        'phone' => '081200000020',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    Booking::query()->create([
        'booking_code' => 'BK-LIST-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_PENDING,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => CarbonImmutable::parse('2026-04-03 14:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 15:00:00'),
        'duration_minutes' => 60,
    ]);

    $this->actingAs($cashier)
        ->get(route('management.bookings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Bookings/Index')
            ->has('bookings', 1)
            ->where('bookings.0.status', Booking::STATUS_PENDING)
        );
});

test('non staff users can not access the internal booking create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('management.bookings.create'))
        ->assertForbidden();
});

test('admins can create internal bookings', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $service = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('management.bookings.store'), [
            'customer_name' => 'Sinta Cue',
            'customer_phone' => '081200000010',
            'customer_email' => 'sinta@example.com',
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'start_at' => '2026-04-03 16:00:00',
            'end_at' => '2026-04-03 18:00:00',
            'notes' => 'Reservasi sore',
        ])
        ->assertRedirect(route('management.bookings.create'));

    $this->assertDatabaseHas('bookings', [
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CONFIRMED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'created_by_user_id' => $admin->id,
    ]);
});

test('valid booking lifecycle transitions succeed', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Status Booking',
        'phone' => '081200000021',
    ]);
    $service = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();

    $booking = Booking::query()->create([
        'booking_code' => 'BK-STATE-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_PENDING,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => CarbonImmutable::parse('2026-04-03 18:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 19:00:00'),
        'duration_minutes' => 60,
    ]);

    $this->actingAs($admin)
        ->patch(route('management.bookings.transition', $booking), [
            'status' => Booking::STATUS_CONFIRMED,
        ])
        ->assertRedirect(route('management.bookings.index'));

    $booking->refresh();
    expect($booking->status)->toBe(Booking::STATUS_CONFIRMED);

    $this->actingAs($admin)
        ->patch(route('management.bookings.transition', $booking), [
            'status' => Booking::STATUS_CHECKED_IN,
        ])
        ->assertRedirect(route('management.bookings.index'));

    $booking->refresh();
    expect($booking->status)->toBe(Booking::STATUS_CHECKED_IN);

    $this->actingAs($admin)
        ->patch(route('management.bookings.transition', $booking), [
            'status' => Booking::STATUS_COMPLETED,
        ])
        ->assertRedirect(route('management.bookings.index'));

    $booking->refresh();
    expect($booking->status)->toBe(Booking::STATUS_COMPLETED);
});

test('invalid booking lifecycle transitions are rejected', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Booking Invalid',
        'phone' => '081200000022',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-02')->firstOrFail();

    $booking = Booking::query()->create([
        'booking_code' => 'BK-STATE-002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_PENDING,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => CarbonImmutable::parse('2026-04-03 20:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 21:00:00'),
        'duration_minutes' => 60,
    ]);

    $this->actingAs($cashier)
        ->from(route('management.bookings.index'))
        ->patch(route('management.bookings.transition', $booking), [
            'status' => Booking::STATUS_COMPLETED,
        ])
        ->assertRedirect(route('management.bookings.index'))
        ->assertSessionHasErrors('status');

    $booking->refresh();
    expect($booking->status)->toBe(Booking::STATUS_PENDING);
});

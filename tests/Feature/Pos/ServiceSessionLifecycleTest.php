<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-04 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('cashiers can access the pos sessions page', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('pos.sessions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos/Sessions/Index')
            ->has('serviceOptions', 5)
            ->has('activeSessions', 0)
        );
});

test('cashiers can start a walk in timed service session', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $this->actingAs($cashier)
        ->post(route('pos.sessions.store'), [
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'customer_name' => 'Walk In Player',
            'customer_phone' => '081300000001',
            'customer_email' => 'walkin@example.com',
        ])
        ->assertRedirect(route('pos.sessions.index'));

    $session = ServiceSession::query()->where('service_unit_id', $unit->id)->firstOrFail();

    expect($session->status)->toBe(ServiceSession::STATUS_ACTIVE)
        ->and($session->booking_id)->toBeNull()
        ->and($session->started_by_user_id)->toBe($cashier->id)
        ->and($session->pricing_snapshot_json)->not->toBeNull()
        ->and($session->pricing_snapshot_json['price_per_interval_rupiah'])->toBe(12000)
        ->and($session->pricing_snapshot_json['day_type'])->toBe('weekend');
});

test('starting a linked booking session checks the booking in', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Booking Player',
        'phone' => '081300000002',
        'email' => 'booking@example.com',
    ]);
    $service = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-POS-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CONFIRMED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-04 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-04 12:00:00'),
        'duration_minutes' => 120,
        'created_by_user_id' => $cashier->id,
    ]);

    $this->actingAs($cashier)
        ->post(route('pos.sessions.store'), [
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
        ])
        ->assertRedirect(route('pos.sessions.index'));

    $booking->refresh();
    $session = ServiceSession::query()->where('booking_id', $booking->id)->firstOrFail();

    expect($booking->status)->toBe(Booking::STATUS_CHECKED_IN)
        ->and($session->customer_id)->toBe($customer->id)
        ->and($session->booking_id)->toBe($booking->id);
});

test('starting a session is rejected when the unit already has an active session', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Active Session Customer',
        'phone' => '081300000003',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    ServiceSession::query()->create([
        'session_code' => 'SS-POS-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => CarbonImmutable::parse('2026-04-04 09:30:00'),
        'billed_minutes' => 0,
        'started_by_user_id' => $cashier->id,
    ]);

    $this->actingAs($cashier)
        ->from(route('pos.sessions.index'))
        ->post(route('pos.sessions.store'), [
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'customer_name' => 'Second Player',
            'customer_phone' => '081300000004',
        ])
        ->assertRedirect(route('pos.sessions.index'))
        ->assertSessionHasErrors('service_unit_id');
});

test('cashiers can stop active sessions and complete linked bookings', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Stop Session Customer',
        'phone' => '081300000005',
    ]);
    $service = Service::query()->where('code', 'rc-adventure')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'rc-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-POS-002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CHECKED_IN,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-04 09:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-04 11:00:00'),
        'duration_minutes' => 120,
        'created_by_user_id' => $cashier->id,
    ]);
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-POS-002',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => CarbonImmutable::parse('2026-04-04 09:15:00'),
        'billed_minutes' => 0,
        'started_by_user_id' => $cashier->id,
    ]);

    $this->actingAs($cashier)
        ->patch(route('pos.sessions.stop', $session))
        ->assertRedirect(route('pos.sessions.index'));

    $session->refresh();
    $booking->refresh();

    expect($session->status)->toBe(ServiceSession::STATUS_COMPLETED)
        ->and($session->closed_by_user_id)->toBe($cashier->id)
        ->and($session->billed_minutes)->toBe(45)
        ->and($booking->status)->toBe(Booking::STATUS_COMPLETED);
});

test('stopping a non active session is rejected', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Completed Session Customer',
        'phone' => '081300000006',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-02')->firstOrFail();
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-POS-003',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-04 08:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-04 09:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $cashier->id,
        'closed_by_user_id' => $cashier->id,
    ]);

    $this->actingAs($cashier)
        ->from(route('pos.sessions.index'))
        ->patch(route('pos.sessions.stop', $session))
        ->assertRedirect(route('pos.sessions.index'))
        ->assertSessionHasErrors('service_session');
});

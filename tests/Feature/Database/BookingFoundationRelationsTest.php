<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Database\Seeders\ServiceCatalogSeeder;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
});

test('customers can own bookings and service sessions', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Budi Player',
        'phone' => '081234567890',
        'email' => 'budi@example.com',
        'notes' => 'Booking VIP',
    ]);

    $booking = Booking::query()->create([
        'booking_code' => 'BK-0001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CONFIRMED,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => now()->addDay(),
        'end_at' => now()->addDay()->addHour(),
        'duration_minutes' => 60,
        'pricing_snapshot_json' => ['price_per_interval_rupiah' => 15000],
        'notes' => 'Near monitor screen',
        'created_by_user_id' => $user->id,
    ]);

    $session = ServiceSession::query()->create([
        'session_code' => 'SS-0001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => now(),
        'billed_minutes' => 0,
        'pricing_snapshot_json' => ['price_per_interval_rupiah' => 15000],
        'started_by_user_id' => $user->id,
    ]);

    expect($customer->bookings)->toHaveCount(1)
        ->and($customer->serviceSessions)->toHaveCount(1)
        ->and($booking->customer->is($customer))->toBeTrue()
        ->and($session->customer->is($customer))->toBeTrue();
});

test('bookings and service sessions belong to services, units, and staff users', function () {
    $service = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();
    $starter = User::factory()->create();
    $closer = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Sari Cue',
        'phone' => '081200000001',
        'email' => 'sari@example.com',
    ]);

    $booking = Booking::query()->create([
        'booking_code' => 'BK-0002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CHECKED_IN,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => now()->addHours(2),
        'end_at' => now()->addHours(4),
        'duration_minutes' => 120,
        'created_by_user_id' => $starter->id,
    ]);

    $session = ServiceSession::query()->create([
        'session_code' => 'SS-0002',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => now()->subHours(2),
        'ended_at' => now(),
        'billed_minutes' => 120,
        'started_by_user_id' => $starter->id,
        'closed_by_user_id' => $closer->id,
    ]);

    expect($service->bookings)->toHaveCount(1)
        ->and($service->serviceSessions)->toHaveCount(1)
        ->and($unit->bookings)->toHaveCount(1)
        ->and($unit->serviceSessions)->toHaveCount(1)
        ->and($booking->service->is($service))->toBeTrue()
        ->and($booking->unit->is($unit))->toBeTrue()
        ->and($booking->createdBy->is($starter))->toBeTrue()
        ->and($session->booking->is($booking))->toBeTrue()
        ->and($session->startedBy->is($starter))->toBeTrue()
        ->and($session->closedBy->is($closer))->toBeTrue();
});

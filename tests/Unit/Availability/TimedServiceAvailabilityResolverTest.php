<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use App\Services\Availability\TimedServiceAvailabilityResolver;
use Carbon\CarbonImmutable;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-02 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('resolver returns active bookable available units for a timed service', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $resolver = app(TimedServiceAvailabilityResolver::class);

    $availableUnits = $resolver->availableUnits(
        $service,
        CarbonImmutable::parse('2026-04-03 14:00:00'),
        CarbonImmutable::parse('2026-04-03 15:00:00'),
    );

    expect($availableUnits->pluck('code')->all())->toBe(['ps-01', 'ps-02']);
});

test('resolver excludes units blocked by overlapping bookings and active sessions', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $firstUnit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $secondUnit = ServiceUnit::query()->where('code', 'ps-02')->firstOrFail();
    $customer = Customer::query()->create([
        'name' => 'Andi Gamer',
        'phone' => '081100000001',
    ]);
    $staff = User::factory()->create();
    $resolver = app(TimedServiceAvailabilityResolver::class);

    Booking::query()->create([
        'booking_code' => 'BK-AV-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $firstUnit->id,
        'status' => Booking::STATUS_CONFIRMED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-03 14:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 15:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);

    ServiceSession::query()->create([
        'session_code' => 'SS-AV-001',
        'service_id' => $service->id,
        'service_unit_id' => $secondUnit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => CarbonImmutable::parse('2026-04-03 13:30:00'),
        'billed_minutes' => 0,
        'started_by_user_id' => $staff->id,
    ]);

    $availableUnits = $resolver->availableUnits(
        $service,
        CarbonImmutable::parse('2026-04-03 14:30:00'),
        CarbonImmutable::parse('2026-04-03 15:30:00'),
    );

    expect($availableUnits)->toHaveCount(0);
});

test('resolver ignores terminal bookings and closed sessions when computing availability', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $firstUnit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $secondUnit = ServiceUnit::query()->where('code', 'ps-02')->firstOrFail();
    $customer = Customer::query()->create([
        'name' => 'Dina Player',
        'phone' => '081100000002',
    ]);
    $staff = User::factory()->create();
    $resolver = app(TimedServiceAvailabilityResolver::class);

    Booking::query()->create([
        'booking_code' => 'BK-AV-002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $firstUnit->id,
        'status' => Booking::STATUS_CANCELLED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-03 14:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 15:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);

    ServiceSession::query()->create([
        'session_code' => 'SS-AV-002',
        'service_id' => $service->id,
        'service_unit_id' => $secondUnit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-03 12:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-03 13:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $staff->id,
        'closed_by_user_id' => $staff->id,
    ]);

    $availableUnits = $resolver->availableUnits(
        $service,
        CarbonImmutable::parse('2026-04-03 14:00:00'),
        CarbonImmutable::parse('2026-04-03 15:00:00'),
    );

    expect($availableUnits->pluck('code')->all())->toBe(['ps-01', 'ps-02']);
});

test('resolver rejects booking windows that violate lead time', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $resolver = app(TimedServiceAvailabilityResolver::class);

    expect(fn () => $resolver->assertBookableWindow(
        $service,
        CarbonImmutable::parse('2026-04-02 10:10:00'),
        CarbonImmutable::parse('2026-04-02 11:10:00'),
    ))->toThrow(ValidationException::class);
});

test('resolver rejects booking windows that do not match slot and duration policy', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $resolver = app(TimedServiceAvailabilityResolver::class);

    expect(fn () => $resolver->assertBookableWindow(
        $service,
        CarbonImmutable::parse('2026-04-03 14:00:00'),
        CarbonImmutable::parse('2026-04-03 14:45:00'),
    ))->toThrow(ValidationException::class);
});

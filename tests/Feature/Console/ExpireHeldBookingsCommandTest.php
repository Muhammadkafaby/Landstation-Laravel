<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceUnit;
use Carbon\CarbonImmutable;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-02 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('cleanup command expires only held bookings that are past their hold expiry', function () {
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $expiredUnit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $activeUnit = ServiceUnit::query()->where('code', 'ps-02')->firstOrFail();
    $customer = Customer::query()->create([
        'name' => 'Held Booker',
        'phone' => '081200000099',
    ]);

    $expiredHold = Booking::query()->create([
        'booking_code' => 'BK-EXPIRE-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $expiredUnit->id,
        'status' => Booking::STATUS_HELD,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => CarbonImmutable::parse('2026-04-03 14:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 15:00:00'),
        'duration_minutes' => 60,
        'hold_expires_at' => CarbonImmutable::parse('2026-04-02 09:59:00'),
    ]);

    $activeHold = Booking::query()->create([
        'booking_code' => 'BK-EXPIRE-002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $activeUnit->id,
        'status' => Booking::STATUS_HELD,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => CarbonImmutable::parse('2026-04-03 16:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-03 17:00:00'),
        'duration_minutes' => 60,
        'hold_expires_at' => CarbonImmutable::parse('2026-04-02 10:15:00'),
    ]);

    expect(Artisan::all())->toHaveKey('bookings:expire-held');

    $this->artisan('bookings:expire-held')
        ->assertExitCode(0);

    $expiredHold->refresh();
    $activeHold->refresh();

    expect($expiredHold->status)->toBe(Booking::STATUS_EXPIRED);
    expect($expiredHold->expired_at?->toDateTimeString())->toBe('2026-04-02 10:00:00');
    expect($activeHold->status)->toBe(Booking::STATUS_HELD);
    expect($activeHold->expired_at)->toBeNull();
});

<?php

use Illuminate\Support\Facades\Schema;

test('booking foundation tables exist', function () {
    expect(Schema::hasTable('customers'))->toBeTrue()
        ->and(Schema::hasTable('bookings'))->toBeTrue()
        ->and(Schema::hasTable('service_sessions'))->toBeTrue();
});

test('booking foundation tables expose the expected overlap-ready columns', function () {
    expect(Schema::hasColumns('customers', [
        'id',
        'name',
        'phone',
        'email',
        'notes',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('bookings', [
            'id',
            'booking_code',
            'customer_id',
            'service_id',
            'service_unit_id',
            'status',
            'booking_source',
            'start_at',
            'end_at',
            'duration_minutes',
            'pricing_snapshot_json',
            'notes',
            'created_by_user_id',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('service_sessions', [
            'id',
            'session_code',
            'service_id',
            'service_unit_id',
            'customer_id',
            'booking_id',
            'status',
            'started_at',
            'ended_at',
            'paused_at',
            'billed_minutes',
            'pricing_snapshot_json',
            'started_by_user_id',
            'closed_by_user_id',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

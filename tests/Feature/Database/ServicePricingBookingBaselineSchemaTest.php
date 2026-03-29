<?php

use Illuminate\Support\Facades\Schema;

test('pricing and booking baseline tables exist', function () {
    expect(Schema::hasTable('service_pricing_rules'))->toBeTrue()
        ->and(Schema::hasTable('service_booking_policies'))->toBeTrue();
});

test('pricing and booking baseline tables expose the expected flexible columns', function () {
    expect(Schema::hasColumns('service_pricing_rules', [
        'id',
        'service_id',
        'service_unit_id',
        'pricing_model',
        'billing_interval_minutes',
        'base_price_rupiah',
        'price_per_interval_rupiah',
        'minimum_charge_rupiah',
        'starts_at',
        'ends_at',
        'priority',
        'is_active',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('service_booking_policies', [
            'id',
            'service_id',
            'slot_interval_minutes',
            'min_duration_minutes',
            'max_duration_minutes',
            'lead_time_minutes',
            'max_advance_days',
            'requires_unit_assignment',
            'walk_in_allowed',
            'online_booking_allowed',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

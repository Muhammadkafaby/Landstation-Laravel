<?php

use Illuminate\Support\Facades\Schema;

test('service catalog baseline tables exist', function () {
    expect(Schema::hasTable('service_categories'))->toBeTrue()
        ->and(Schema::hasTable('services'))->toBeTrue()
        ->and(Schema::hasTable('service_units'))->toBeTrue();
});

test('service catalog baseline tables expose the expected flexible columns', function () {
    expect(Schema::hasColumns('service_categories', [
        'id',
        'code',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('services', [
            'id',
            'service_category_id',
            'code',
            'name',
            'slug',
            'service_type',
            'billing_type',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('service_units', [
            'id',
            'service_id',
            'code',
            'name',
            'zone',
            'status',
            'capacity',
            'is_bookable',
            'is_active',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

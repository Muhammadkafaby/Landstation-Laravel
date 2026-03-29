<?php

use Illuminate\Support\Facades\Schema;

test('cafe order foundation tables exist', function () {
    expect(Schema::hasTable('product_categories'))->toBeTrue()
        ->and(Schema::hasTable('products'))->toBeTrue()
        ->and(Schema::hasTable('orders'))->toBeTrue()
        ->and(Schema::hasTable('order_items'))->toBeTrue();
});

test('cafe order foundation tables expose the expected pricing and snapshot columns', function () {
    expect(Schema::hasColumns('product_categories', [
        'id',
        'code',
        'name',
        'is_active',
        'sort_order',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('products', [
            'id',
            'product_category_id',
            'sku',
            'name',
            'product_type',
            'price_rupiah',
            'cost_rupiah',
            'is_active',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('orders', [
            'id',
            'order_code',
            'customer_id',
            'booking_id',
            'service_session_id',
            'status',
            'ordered_at',
            'created_by_user_id',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('order_items', [
            'id',
            'order_id',
            'product_id',
            'qty',
            'unit_price_rupiah',
            'subtotal_rupiah',
            'item_snapshot_json',
            'notes',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

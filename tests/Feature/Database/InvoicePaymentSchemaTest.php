<?php

use Illuminate\Support\Facades\Schema;

test('invoice and payment foundation tables exist', function () {
    expect(Schema::hasTable('payment_methods'))->toBeTrue()
        ->and(Schema::hasTable('invoices'))->toBeTrue()
        ->and(Schema::hasTable('invoice_lines'))->toBeTrue()
        ->and(Schema::hasTable('payments'))->toBeTrue();
});

test('invoice and payment foundation tables expose the expected monetary and snapshot columns', function () {
    expect(Schema::hasColumns('payment_methods', [
        'id',
        'code',
        'name',
        'channel',
        'is_active',
        'sort_order',
        'metadata_json',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('invoices', [
            'id',
            'invoice_code',
            'customer_id',
            'booking_id',
            'service_session_id',
            'status',
            'subtotal_rupiah',
            'discount_rupiah',
            'tax_rupiah',
            'grand_total_rupiah',
            'issued_at',
            'closed_at',
            'created_by_user_id',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('invoice_lines', [
            'id',
            'invoice_id',
            'line_type',
            'reference_type',
            'reference_id',
            'description',
            'qty',
            'unit_price_rupiah',
            'subtotal_rupiah',
            'snapshot_json',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('payments', [
            'id',
            'invoice_id',
            'payment_method_code',
            'status',
            'amount_rupiah',
            'paid_at',
            'reference_number',
            'verified_by_user_id',
            'notes',
            'payload_json',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

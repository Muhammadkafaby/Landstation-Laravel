<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use App\Services\Payments\ManualPaymentVerifier;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-06 13:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('manual payment verifier can verify cash payments and close invoices when fully paid', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Cash Payment Customer',
        'phone' => '081600000003',
    ]);
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-PAY-001',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 66000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 66000,
        'issued_at' => CarbonImmutable::parse('2026-04-06 12:45:00'),
        'created_by_user_id' => $cashier->id,
    ]);

    $payment = app(ManualPaymentVerifier::class)->verify(
        $invoice,
        PaymentMethod::CASH,
        66000,
        $cashier,
        'CASH-INV-001',
        'Paid in cash',
    );

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->status)->toBe(Payment::STATUS_VERIFIED)
        ->and($payment->payment_method_code)->toBe(PaymentMethod::CASH);

    $invoice->refresh();
    expect($invoice->status)->toBe(Invoice::STATUS_PAID)
        ->and($invoice->closed_at)->not->toBeNull();
});

test('manual payment verifier can verify qris manual payments', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'QRIS Payment Customer',
        'phone' => '081600000004',
    ]);
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-PAY-002',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 50000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 50000,
        'issued_at' => CarbonImmutable::parse('2026-04-06 12:50:00'),
        'created_by_user_id' => $cashier->id,
    ]);

    $payment = app(ManualPaymentVerifier::class)->verify(
        $invoice,
        PaymentMethod::QRIS_MANUAL,
        50000,
        $cashier,
        'QRIS-001',
        'Static QR confirmed by cashier',
    );

    expect($payment->payment_method_code)->toBe(PaymentMethod::QRIS_MANUAL)
        ->and($payment->reference_number)->toBe('QRIS-001');
});

test('manual payment verifier rejects overpayment beyond invoice remaining total', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-PAY-003',
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 30000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 30000,
        'issued_at' => CarbonImmutable::parse('2026-04-06 12:55:00'),
        'created_by_user_id' => $cashier->id,
    ]);

    expect(fn () => app(ManualPaymentVerifier::class)->verify(
        $invoice,
        PaymentMethod::CASH,
        35000,
        $cashier,
    ))->toThrow(ValidationException::class);
});

test('manual payment verifier rejects users without manage payments permission', function () {
    $user = User::factory()->create();
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-PAY-004',
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 20000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 20000,
        'issued_at' => CarbonImmutable::parse('2026-04-06 12:57:00'),
    ]);

    expect(fn () => app(ManualPaymentVerifier::class)->verify(
        $invoice,
        PaymentMethod::CASH,
        20000,
        $user,
    ))->toThrow(ValidationException::class);
});

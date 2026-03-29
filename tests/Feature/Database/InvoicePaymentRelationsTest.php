<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ServiceCatalogSeeder;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
});

test('invoices can belong to customer booking service session and staff creator', function () {
    $customer = Customer::query()->create([
        'name' => 'Invoice Customer',
        'phone' => '081500000001',
    ]);
    $staff = User::factory()->create();
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-INV-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_COMPLETED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-05 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-05 11:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-INV-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-05 10:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-05 11:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $staff->id,
        'closed_by_user_id' => $staff->id,
    ]);

    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-001',
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'service_session_id' => $session->id,
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 50000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 50000,
        'issued_at' => CarbonImmutable::parse('2026-04-05 11:05:00'),
        'created_by_user_id' => $staff->id,
    ]);

    $invoice->lines()->create([
        'line_type' => 'service_session',
        'reference_type' => ServiceSession::class,
        'reference_id' => $session->id,
        'description' => 'PlayStation Regular Session',
        'qty' => 60,
        'unit_price_rupiah' => 15000,
        'subtotal_rupiah' => 50000,
        'snapshot_json' => [
            'session_code' => $session->session_code,
            'billed_minutes' => $session->billed_minutes,
        ],
    ]);

    expect($customer->invoices)->toHaveCount(1)
        ->and($booking->invoices)->toHaveCount(1)
        ->and($session->invoices)->toHaveCount(1)
        ->and($staff->createdInvoices)->toHaveCount(1)
        ->and($invoice->lines)->toHaveCount(1);
});

test('payments can belong to invoices payment methods and verifier users', function () {
    $customer = Customer::query()->create([
        'name' => 'Payment Customer',
        'phone' => '081500000002',
    ]);
    $staff = User::factory()->create();
    $paymentMethod = PaymentMethod::query()->where('code', 'cash')->firstOrFail();
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-002',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 30000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 30000,
        'issued_at' => CarbonImmutable::parse('2026-04-05 12:00:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-05 12:10:00'),
        'created_by_user_id' => $staff->id,
    ]);

    $payment = Payment::query()->create([
        'invoice_id' => $invoice->id,
        'payment_method_code' => $paymentMethod->code,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 30000,
        'paid_at' => CarbonImmutable::parse('2026-04-05 12:10:00'),
        'reference_number' => 'CASH-001',
        'verified_by_user_id' => $staff->id,
        'notes' => 'Paid in cash',
        'payload_json' => ['drawer' => 'main-cashier'],
    ]);

    expect($invoice->payments)->toHaveCount(1)
        ->and($payment->invoice->is($invoice))->toBeTrue()
        ->and($payment->paymentMethod->is($paymentMethod))->toBeTrue()
        ->and($payment->verifiedBy->is($staff))->toBeTrue();
});

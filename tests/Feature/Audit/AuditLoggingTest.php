<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use App\Services\Booking\BookingStatusManager;
use App\Services\Payments\ManualPaymentVerifier;
use App\Services\Sessions\ServiceSessionService;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ServiceCatalogSeeder;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-10 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('payment verification writes an audit log entry', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-AUDIT-001',
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 30000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 30000,
        'issued_at' => CarbonImmutable::parse('2026-04-10 09:50:00'),
        'created_by_user_id' => $cashier->id,
    ]);

    app(ManualPaymentVerifier::class)->verify(
        $invoice,
        PaymentMethod::CASH,
        30000,
        $cashier,
        'AUDIT-CASH-001',
        'Audit payment test',
    );

    $this->assertDatabaseHas('audit_logs', [
        'actor_user_id' => $cashier->id,
        'action' => 'payment.verified',
        'auditable_type' => Invoice::class,
        'auditable_id' => $invoice->id,
    ]);
});

test('booking status transition writes an audit log entry', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Audit Booking Customer',
        'phone' => '082100000001',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-AUDIT-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_PENDING,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-10 12:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-10 13:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $admin->id,
    ]);

    app(BookingStatusManager::class)->transition($booking, Booking::STATUS_CONFIRMED, $admin);

    $this->assertDatabaseHas('audit_logs', [
        'actor_user_id' => $admin->id,
        'action' => 'booking.status_transitioned',
        'auditable_type' => Booking::class,
        'auditable_id' => $booking->id,
    ]);
});

test('service session start and stop write audit log entries', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $session = app(ServiceSessionService::class)->start([
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_name' => 'Audit Walk In',
        'customer_phone' => '082100000002',
        'customer_email' => 'audit-walkin@example.com',
    ], $cashier);

    $this->assertDatabaseHas('audit_logs', [
        'actor_user_id' => $cashier->id,
        'action' => 'service_session.started',
        'auditable_type' => ServiceSession::class,
        'auditable_id' => $session->id,
    ]);

    $this->travelTo(CarbonImmutable::parse('2026-04-10 10:45:00'));

    app(ServiceSessionService::class)->stop($session, $cashier);

    $this->assertDatabaseHas('audit_logs', [
        'actor_user_id' => $cashier->id,
        'action' => 'service_session.stopped',
        'auditable_type' => ServiceSession::class,
        'auditable_id' => $session->id,
    ]);
});

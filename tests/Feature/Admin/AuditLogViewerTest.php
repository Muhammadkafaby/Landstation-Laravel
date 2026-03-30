<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-11 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

function seedAuditViewerFixture(): array
{
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Audit Viewer Customer',
        'phone' => '082200000001',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-AUDIT-VIEW-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CONFIRMED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-11 12:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-11 13:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $admin->id,
    ]);
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-AUDIT-VIEW-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-11 08:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-11 09:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $cashier->id,
        'closed_by_user_id' => $cashier->id,
    ]);
    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-AUDIT-VIEW-001',
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'service_session_id' => $session->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 50000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 50000,
        'issued_at' => CarbonImmutable::parse('2026-04-11 09:05:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-11 09:10:00'),
        'created_by_user_id' => $cashier->id,
    ]);

    AuditLog::query()->create([
        'actor_user_id' => $cashier->id,
        'action' => 'service_session.started',
        'auditable_type' => ServiceSession::class,
        'auditable_id' => $session->id,
        'context_json' => [
            'service_unit_id' => $unit->id,
        ],
        'created_at' => CarbonImmutable::parse('2026-04-11 08:00:00'),
        'updated_at' => CarbonImmutable::parse('2026-04-11 08:00:00'),
    ]);

    AuditLog::query()->create([
        'actor_user_id' => $admin->id,
        'action' => 'booking.status_transitioned',
        'auditable_type' => Booking::class,
        'auditable_id' => $booking->id,
        'context_json' => [
            'from_status' => 'pending',
            'to_status' => 'confirmed',
        ],
        'created_at' => CarbonImmutable::parse('2026-04-11 07:55:00'),
        'updated_at' => CarbonImmutable::parse('2026-04-11 07:55:00'),
    ]);

    AuditLog::query()->create([
        'actor_user_id' => $cashier->id,
        'action' => 'payment.verified',
        'auditable_type' => Invoice::class,
        'auditable_id' => $invoice->id,
        'context_json' => [
            'amount_rupiah' => 50000,
            'payment_method_code' => 'cash',
        ],
        'created_at' => CarbonImmutable::parse('2026-04-11 09:10:00'),
        'updated_at' => CarbonImmutable::parse('2026-04-11 09:10:00'),
    ]);

    return [$admin, $cashier, $booking, $session, $invoice];
}

test('admins can access audit log viewer with paginated entries', function () {
    [$admin] = seedAuditViewerFixture();

    $this->actingAs($admin)
        ->get(route('reports.audit.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Audit/Index')
            ->has('logs.data', 3)
            ->where('logs.data.0.action', 'payment.verified')
            ->where('logs.data.1.action', 'booking.status_transitioned')
            ->where('logs.data.2.action', 'service_session.started')
        );
});

test('admins can filter audit log viewer by action and actor', function () {
    [$admin, $cashier] = seedAuditViewerFixture();

    $this->actingAs($admin)
        ->get(route('reports.audit.index', [
            'action' => 'payment.verified',
            'actor' => (string) $cashier->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Audit/Index')
            ->where('filters.action', 'payment.verified')
            ->where('filters.actor', (string) $cashier->id)
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'payment.verified')
        );
});

test('admins can export filtered audit logs as csv', function () {
    [$admin] = seedAuditViewerFixture();

    $response = $this->actingAs($admin)
        ->get(route('reports.audit.export', ['action' => 'payment.verified']));

    $response
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=audit-logs.csv');

    $csv = $response->getContent();

    expect($csv)
        ->toContain('created_at,actor_name,action,auditable_type,auditable_id,context_json')
        ->toContain('payment.verified')
        ->not->toContain('booking.status_transitioned');
});

test('cashiers can not access or export audit log viewer', function () {
    [, $cashier] = seedAuditViewerFixture();

    $this->actingAs($cashier)
        ->get(route('reports.audit.index'))
        ->assertForbidden();

    $this->actingAs($cashier)
        ->get(route('reports.audit.export'))
        ->assertForbidden();
});

test('non staff users can not access or export audit log viewer', function () {
    seedAuditViewerFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.audit.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.audit.export'))
        ->assertForbidden();
});

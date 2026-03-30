<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(ProductCatalogSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-07 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

function seedReportsFixture(User $staff): void
{
    $customer = Customer::query()->create([
        'name' => 'Reports Customer',
        'phone' => '081800000001',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $statuses = [
        Booking::STATUS_PENDING,
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_CHECKED_IN,
        Booking::STATUS_COMPLETED,
        Booking::STATUS_CANCELLED,
        Booking::STATUS_NO_SHOW,
    ];

    foreach ($statuses as $index => $status) {
        Booking::query()->create([
            'booking_code' => sprintf('BK-REPORT-%03d', $index + 1),
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'status' => $status,
            'booking_source' => Booking::SOURCE_ADMIN,
            'start_at' => CarbonImmutable::parse('2026-04-07 10:00:00')->addHours($index),
            'end_at' => CarbonImmutable::parse('2026-04-07 11:00:00')->addHours($index),
            'duration_minutes' => 60,
            'created_by_user_id' => $staff->id,
        ]);
    }

    ServiceSession::query()->create([
        'session_code' => 'SS-REPORT-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => CarbonImmutable::parse('2026-04-07 09:00:00'),
        'billed_minutes' => 0,
        'started_by_user_id' => $staff->id,
    ]);
    ServiceSession::query()->create([
        'session_code' => 'SS-REPORT-002',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_PAUSED,
        'started_at' => CarbonImmutable::parse('2026-04-07 08:00:00'),
        'paused_at' => CarbonImmutable::parse('2026-04-07 08:30:00'),
        'billed_minutes' => 30,
        'started_by_user_id' => $staff->id,
    ]);
    ServiceSession::query()->create([
        'session_code' => 'SS-REPORT-003',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-07 06:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-07 07:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $staff->id,
        'closed_by_user_id' => $staff->id,
    ]);

    Order::query()->create([
        'order_code' => 'ORD-REPORT-001',
        'customer_id' => $customer->id,
        'status' => Order::STATUS_SUBMITTED,
        'ordered_at' => CarbonImmutable::parse('2026-04-07 09:30:00'),
        'created_by_user_id' => $staff->id,
    ]);
    Order::query()->create([
        'order_code' => 'ORD-REPORT-002',
        'customer_id' => $customer->id,
        'status' => Order::STATUS_COMPLETED,
        'ordered_at' => CarbonImmutable::parse('2026-04-07 08:30:00'),
        'created_by_user_id' => $staff->id,
    ]);
    Order::query()->create([
        'order_code' => 'ORD-REPORT-003',
        'customer_id' => $customer->id,
        'status' => Order::STATUS_CANCELLED,
        'ordered_at' => CarbonImmutable::parse('2026-04-07 07:30:00'),
        'created_by_user_id' => $staff->id,
    ]);

    $openInvoice = Invoice::query()->create([
        'invoice_code' => 'INV-REPORT-001',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 45000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 45000,
        'issued_at' => CarbonImmutable::parse('2026-04-07 09:40:00'),
        'created_by_user_id' => $staff->id,
    ]);

    $paidInvoice = Invoice::query()->create([
        'invoice_code' => 'INV-REPORT-002',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 96000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 96000,
        'issued_at' => CarbonImmutable::parse('2026-04-07 08:40:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-07 08:50:00'),
        'created_by_user_id' => $staff->id,
    ]);

    Payment::query()->create([
        'invoice_id' => $paidInvoice->id,
        'payment_method_code' => PaymentMethod::CASH,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 30000,
        'paid_at' => CarbonImmutable::parse('2026-04-07 08:45:00'),
        'verified_by_user_id' => $staff->id,
    ]);
    Payment::query()->create([
        'invoice_id' => $paidInvoice->id,
        'payment_method_code' => PaymentMethod::QRIS_MANUAL,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 66000,
        'paid_at' => CarbonImmutable::parse('2026-04-07 08:46:00'),
        'verified_by_user_id' => $staff->id,
    ]);
    Payment::query()->create([
        'invoice_id' => $openInvoice->id,
        'payment_method_code' => PaymentMethod::CASH,
        'status' => Payment::STATUS_PENDING,
        'amount_rupiah' => 10000,
    ]);
}

test('admins can access reports page with operational summary props', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    seedReportsFixture($admin);

    $this->actingAs($admin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Index')
            ->where('summary.bookingsTotal', 6)
            ->where('summary.activeSessions', 2)
            ->where('summary.completedSessions', 1)
            ->where('summary.submittedOrders', 1)
            ->where('summary.completedOrders', 1)
            ->where('summary.openInvoices', 1)
            ->where('summary.paidInvoices', 1)
            ->where('summary.verifiedRevenueRupiah', 96000)
            ->where('bookingSummary.pending', 1)
            ->where('bookingSummary.noShow', 1)
            ->where('paymentMethodSummary.cashRupiah', 30000)
            ->where('paymentMethodSummary.qrisManualRupiah', 66000)
        );
});

test('admins can filter reports summary to today only', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    seedReportsFixture($admin);

    $customer = Customer::query()->create([
        'name' => 'Older Reports Customer',
        'phone' => '081800000999',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    Booking::query()->create([
        'booking_code' => 'BK-REPORT-OLD-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_COMPLETED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-02 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-02 11:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $admin->id,
    ]);

    ServiceSession::query()->create([
        'session_code' => 'SS-REPORT-OLD-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-02 08:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-02 09:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $admin->id,
        'closed_by_user_id' => $admin->id,
    ]);

    Order::query()->create([
        'order_code' => 'ORD-REPORT-OLD-001',
        'customer_id' => $customer->id,
        'status' => Order::STATUS_COMPLETED,
        'ordered_at' => CarbonImmutable::parse('2026-04-02 08:30:00'),
        'created_by_user_id' => $admin->id,
    ]);

    $olderInvoice = Invoice::query()->create([
        'invoice_code' => 'INV-REPORT-OLD-001',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 20000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 20000,
        'issued_at' => CarbonImmutable::parse('2026-04-02 08:40:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-02 08:50:00'),
        'created_by_user_id' => $admin->id,
    ]);

    Payment::query()->create([
        'invoice_id' => $olderInvoice->id,
        'payment_method_code' => PaymentMethod::CASH,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 20000,
        'paid_at' => CarbonImmutable::parse('2026-04-02 08:45:00'),
        'verified_by_user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('reports.index', ['date_scope' => 'today']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Index')
            ->where('filters.date_scope', 'today')
            ->where('summary.bookingsTotal', 6)
            ->where('summary.completedSessions', 1)
            ->where('summary.completedOrders', 1)
            ->where('summary.paidInvoices', 1)
            ->where('summary.verifiedRevenueRupiah', 96000)
            ->where('paymentMethodSummary.cashRupiah', 30000)
        );
});

test('admins can filter reports summary to the last seven days', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    seedReportsFixture($admin);

    $customer = Customer::query()->create([
        'name' => 'Rolling Window Customer',
        'phone' => '081800000998',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    Booking::query()->create([
        'booking_code' => 'BK-REPORT-W7-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_COMPLETED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-02 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-02 11:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $admin->id,
    ]);

    ServiceSession::query()->create([
        'session_code' => 'SS-REPORT-W7-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-02 08:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-02 09:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $admin->id,
        'closed_by_user_id' => $admin->id,
    ]);

    Order::query()->create([
        'order_code' => 'ORD-REPORT-W7-001',
        'customer_id' => $customer->id,
        'status' => Order::STATUS_COMPLETED,
        'ordered_at' => CarbonImmutable::parse('2026-04-02 08:30:00'),
        'created_by_user_id' => $admin->id,
    ]);

    $rollingInvoice = Invoice::query()->create([
        'invoice_code' => 'INV-REPORT-W7-001',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 20000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 20000,
        'issued_at' => CarbonImmutable::parse('2026-04-02 08:40:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-02 08:50:00'),
        'created_by_user_id' => $admin->id,
    ]);

    Payment::query()->create([
        'invoice_id' => $rollingInvoice->id,
        'payment_method_code' => PaymentMethod::CASH,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 20000,
        'paid_at' => CarbonImmutable::parse('2026-04-02 08:45:00'),
        'verified_by_user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('reports.index', ['date_scope' => 'last_7_days']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Index')
            ->where('filters.date_scope', 'last_7_days')
            ->where('summary.bookingsTotal', 7)
            ->where('summary.completedSessions', 2)
            ->where('summary.completedOrders', 2)
            ->where('summary.paidInvoices', 2)
            ->where('summary.verifiedRevenueRupiah', 116000)
            ->where('paymentMethodSummary.cashRupiah', 50000)
        );
});

test('admins can export reports summary as csv using the current date scope', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    seedReportsFixture($admin);

    $response = $this->actingAs($admin)
        ->get(route('reports.export', ['date_scope' => 'today']));

    $response
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=reports-summary.csv');

    $csv = $response->getContent();

    expect($csv)
        ->toContain('section,metric,value,date_scope')
        ->toContain('summary,bookingsTotal,6,today')
        ->toContain('summary,verifiedRevenueRupiah,96000,today')
        ->toContain('bookingSummary,noShow,1,today')
        ->toContain('paymentMethodSummary,qrisManualRupiah,66000,today')
        ->toContain('invoiceSummary,paidTotalRupiah,96000,today');
});

test('cashiers can not access reports page', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('reports.index'))
        ->assertForbidden();

    $this->actingAs($cashier)
        ->get(route('reports.export'))
        ->assertForbidden();
});

test('non staff users can not access reports page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.export'))
        ->assertForbidden();
});

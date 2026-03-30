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
    $this->travelTo(CarbonImmutable::parse('2026-04-08 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

function createCustomerHistoryFixture(User $staff): Customer
{
    $customer = Customer::query()->create([
        'name' => 'History Customer',
        'phone' => '081900000001',
        'email' => 'history@example.com',
        'notes' => 'Regular member',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $bookingOne = Booking::query()->create([
        'booking_code' => 'BK-HISTORY-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_COMPLETED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-08 08:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-08 09:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);

    Booking::query()->create([
        'booking_code' => 'BK-HISTORY-002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CANCELLED,
        'booking_source' => Booking::SOURCE_PUBLIC,
        'start_at' => CarbonImmutable::parse('2026-04-09 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-09 11:00:00'),
        'duration_minutes' => 60,
    ]);

    $session = ServiceSession::query()->create([
        'session_code' => 'SS-HISTORY-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $bookingOne->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-08 08:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-08 09:00:00'),
        'billed_minutes' => 60,
        'started_by_user_id' => $staff->id,
        'closed_by_user_id' => $staff->id,
    ]);

    $order = Order::query()->create([
        'order_code' => 'ORD-HISTORY-001',
        'customer_id' => $customer->id,
        'booking_id' => $bookingOne->id,
        'service_session_id' => $session->id,
        'status' => Order::STATUS_COMPLETED,
        'ordered_at' => CarbonImmutable::parse('2026-04-08 08:30:00'),
        'created_by_user_id' => $staff->id,
    ]);

    $invoice = Invoice::query()->create([
        'invoice_code' => 'INV-HISTORY-001',
        'customer_id' => $customer->id,
        'booking_id' => $bookingOne->id,
        'service_session_id' => $session->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 66000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 66000,
        'issued_at' => CarbonImmutable::parse('2026-04-08 09:05:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-08 09:10:00'),
        'created_by_user_id' => $staff->id,
    ]);

    Payment::query()->create([
        'invoice_id' => $invoice->id,
        'payment_method_code' => PaymentMethod::CASH,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 66000,
        'paid_at' => CarbonImmutable::parse('2026-04-08 09:10:00'),
        'reference_number' => 'CASH-HISTORY-001',
        'verified_by_user_id' => $staff->id,
    ]);

    return $customer->fresh();
}

test('admins can access customer history list with aggregated activity summaries', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    createCustomerHistoryFixture($admin);

    $this->actingAs($admin)
        ->get(route('reports.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Index')
            ->has('customers.data', 1)
            ->where('customers.total', 1)
            ->where('customers.data.0.name', 'History Customer')
            ->where('customers.data.0.bookingsCount', 2)
            ->where('customers.data.0.sessionsCount', 1)
            ->where('customers.data.0.ordersCount', 1)
            ->where('customers.data.0.invoicesCount', 1)
            ->where('customers.data.0.verifiedPaymentsRupiah', 66000)
        );
});

test('customer history index paginates customer summaries', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createCustomerHistoryFixture($admin);

    foreach (range(1, 16) as $index) {
        Customer::query()->create([
            'name' => sprintf('Paged Customer %02d', $index),
            'phone' => sprintf('081911100%03d', $index),
            'email' => sprintf('paged%02d@example.com', $index),
        ]);
    }

    $this->actingAs($admin)
        ->get(route('reports.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Index')
            ->has('customers.data', 15)
            ->where('customers.current_page', 1)
            ->where('customers.per_page', 15)
            ->where('customers.total', 17)
        );
});

test('admins can search customer history by customer name', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createCustomerHistoryFixture($admin);
    Customer::query()->create([
        'name' => 'Another Member',
        'phone' => '081900000999',
        'email' => 'other@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('reports.customers.index', ['q' => 'History']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Index')
            ->where('filters.q', 'History')
            ->has('customers.data', 1)
            ->where('customers.data.0.name', 'History Customer')
        );
});

test('customer history pagination preserves search query', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createCustomerHistoryFixture($admin);

    foreach (range(1, 16) as $index) {
        Customer::query()->create([
            'name' => sprintf('History Extra %02d', $index),
            'phone' => sprintf('081922200%03d', $index),
            'email' => sprintf('history-extra%02d@example.com', $index),
        ]);
    }

    $this->actingAs($admin)
        ->get(route('reports.customers.index', ['q' => 'History', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Index')
            ->where('filters.q', 'History')
            ->where('customers.current_page', 2)
            ->where('customers.total', 17)
            ->has('customers.data', 2)
        );
});

test('admins can search customer history by phone or email', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createCustomerHistoryFixture($admin);
    Customer::query()->create([
        'name' => 'Different Member',
        'phone' => '081900000555',
        'email' => 'different@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('reports.customers.index', ['q' => 'different@example.com']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Index')
            ->where('filters.q', 'different@example.com')
            ->has('customers.data', 1)
            ->where('customers.data.0.name', 'Different Member')
        );
});

test('admins can export filtered customer history as csv', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createCustomerHistoryFixture($admin);
    Customer::query()->create([
        'name' => 'Different Member',
        'phone' => '081900000555',
        'email' => 'different@example.com',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('reports.customers.export', ['q' => 'History']));

    $response
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=customer-history.csv');

    $lines = preg_split("/\r\n|\n|\r/", trim($response->getContent()));

    expect(str_getcsv($lines[0]))->toBe([
        'name',
        'phone',
        'email',
        'bookings_count',
        'sessions_count',
        'orders_count',
        'invoices_count',
        'verified_payments_rupiah',
        'last_activity_at',
    ]);

    expect(str_getcsv($lines[1]))->toBe([
        'History Customer',
        '081900000001',
        'history@example.com',
        '2',
        '1',
        '1',
        '1',
        '66000',
        '2026-04-08T10:00:00+00:00',
    ])
        ->and(implode("\n", $lines))->not->toContain('Different Member');
});

test('admins can access customer history detail with booking session order and invoice timelines', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $customer = createCustomerHistoryFixture($admin);

    $this->actingAs($admin)
        ->get(route('reports.customers.show', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Show')
            ->where('customer.name', 'History Customer')
            ->where('customer.verifiedPaymentsRupiah', 66000)
            ->has('bookings', 2)
            ->has('sessions', 1)
            ->has('orders', 1)
            ->has('invoices', 1)
            ->where('bookings.0.status', Booking::STATUS_CANCELLED)
            ->where('sessions.0.status', ServiceSession::STATUS_COMPLETED)
            ->where('orders.0.status', Order::STATUS_COMPLETED)
            ->where('invoices.0.status', Invoice::STATUS_PAID)
            ->where('invoices.0.payments.0.paymentMethodCode', PaymentMethod::CASH)
        );
});

test('cashiers can not access customer history pages', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Blocked Customer',
        'phone' => '081900000002',
    ]);

    $this->actingAs($cashier)
        ->get(route('reports.customers.index'))
        ->assertForbidden();

    $this->actingAs($cashier)
        ->get(route('reports.customers.show', $customer))
        ->assertForbidden();

    $this->actingAs($cashier)
        ->get(route('reports.customers.export'))
        ->assertForbidden();
});

test('non staff users can not access customer history pages', function () {
    $user = User::factory()->create();
    $customer = Customer::query()->create([
        'name' => 'Blocked Non Staff',
        'phone' => '081900000003',
    ]);

    $this->actingAs($user)
        ->get(route('reports.customers.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.customers.show', $customer))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.customers.export'))
        ->assertForbidden();
});

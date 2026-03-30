<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-09 09:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

function createTransactionLedgerFixture(User $staff): void
{
    $customer = Customer::query()->create([
        'name' => 'Ledger Customer',
        'phone' => '082000000001',
        'email' => 'ledger@example.com',
    ]);

    $olderInvoice = Invoice::query()->create([
        'invoice_code' => 'INV-LEDGER-001',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_OPEN,
        'subtotal_rupiah' => 50000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 50000,
        'issued_at' => CarbonImmutable::parse('2026-04-09 07:00:00'),
        'created_by_user_id' => $staff->id,
    ]);

    InvoiceLine::query()->create([
        'invoice_id' => $olderInvoice->id,
        'line_type' => 'service_session',
        'reference_type' => 'service_session',
        'reference_id' => 101,
        'description' => 'PlayStation Session',
        'qty' => 60,
        'unit_price_rupiah' => 833,
        'subtotal_rupiah' => 50000,
        'snapshot_json' => ['session_code' => 'SS-LEDGER-001'],
    ]);

    Payment::query()->create([
        'invoice_id' => $olderInvoice->id,
        'payment_method_code' => PaymentMethod::CASH,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 20000,
        'paid_at' => CarbonImmutable::parse('2026-04-09 07:10:00'),
        'verified_by_user_id' => $staff->id,
    ]);

    Payment::query()->create([
        'invoice_id' => $olderInvoice->id,
        'payment_method_code' => PaymentMethod::QRIS_MANUAL,
        'status' => Payment::STATUS_PENDING,
        'amount_rupiah' => 30000,
        'paid_at' => null,
        'verified_by_user_id' => null,
    ]);

    $newerInvoice = Invoice::query()->create([
        'invoice_code' => 'INV-LEDGER-002',
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 66000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 66000,
        'issued_at' => CarbonImmutable::parse('2026-04-09 08:00:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-09 08:15:00'),
        'created_by_user_id' => $staff->id,
    ]);

    InvoiceLine::query()->create([
        'invoice_id' => $newerInvoice->id,
        'line_type' => 'order_item',
        'reference_type' => 'order_item',
        'reference_id' => 202,
        'description' => 'Americano',
        'qty' => 2,
        'unit_price_rupiah' => 18000,
        'subtotal_rupiah' => 36000,
        'snapshot_json' => ['sku' => 'cafe-americano'],
    ]);

    Payment::query()->create([
        'invoice_id' => $newerInvoice->id,
        'payment_method_code' => PaymentMethod::QRIS_MANUAL,
        'status' => Payment::STATUS_VERIFIED,
        'amount_rupiah' => 66000,
        'paid_at' => CarbonImmutable::parse('2026-04-09 08:10:00'),
        'verified_by_user_id' => $staff->id,
    ]);
}

test('admins can access transaction ledger with invoice drill down props', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createTransactionLedgerFixture($admin);

    $this->actingAs($admin)
        ->get(route('reports.transactions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Transactions/Index')
            ->has('ledger.data', 2)
            ->where('ledger.total', 2)
            ->where('ledger.data.0.invoiceCode', 'INV-LEDGER-002')
            ->where('ledger.data.0.status', Invoice::STATUS_PAID)
            ->where('ledger.data.0.verifiedPaidRupiah', 66000)
            ->where('ledger.data.0.remainingBalanceRupiah', 0)
            ->has('ledger.data.0.lines', 1)
            ->has('ledger.data.0.payments', 1)
            ->where('ledger.data.1.invoiceCode', 'INV-LEDGER-001')
            ->where('ledger.data.1.verifiedPaidRupiah', 20000)
            ->where('ledger.data.1.remainingBalanceRupiah', 30000)
        );
});

test('transaction ledger paginates invoice summaries', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createTransactionLedgerFixture($admin);

    foreach (range(1, 14) as $index) {
        Invoice::query()->create([
            'invoice_code' => sprintf('INV-PAGE-%03d', $index),
            'status' => Invoice::STATUS_OPEN,
            'subtotal_rupiah' => 10000,
            'discount_rupiah' => 0,
            'tax_rupiah' => 0,
            'grand_total_rupiah' => 10000,
            'issued_at' => CarbonImmutable::parse('2026-04-09 06:00:00')->subMinutes($index),
            'created_by_user_id' => $admin->id,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('reports.transactions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Transactions/Index')
            ->has('ledger.data', 15)
            ->where('ledger.current_page', 1)
            ->where('ledger.per_page', 15)
            ->where('ledger.total', 16)
        );
});

test('admins can search transaction ledger by invoice code or customer name', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createTransactionLedgerFixture($admin);

    $this->actingAs($admin)
        ->get(route('reports.transactions.index', ['q' => 'LEDGER-002']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Transactions/Index')
            ->where('filters.q', 'LEDGER-002')
            ->has('ledger.data', 1)
            ->where('ledger.data.0.invoiceCode', 'INV-LEDGER-002')
        );
});

test('transaction ledger pagination preserves active filters', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createTransactionLedgerFixture($admin);

    foreach (range(1, 15) as $index) {
        Invoice::query()->create([
            'invoice_code' => sprintf('INV-PAID-%03d', $index),
            'status' => Invoice::STATUS_PAID,
            'subtotal_rupiah' => 15000,
            'discount_rupiah' => 0,
            'tax_rupiah' => 0,
            'grand_total_rupiah' => 15000,
            'issued_at' => CarbonImmutable::parse('2026-04-09 05:00:00')->subMinutes($index),
            'created_by_user_id' => $admin->id,
        ]);
        Payment::query()->create([
            'invoice_id' => Invoice::query()->where('invoice_code', sprintf('INV-PAID-%03d', $index))->value('id'),
            'payment_method_code' => PaymentMethod::QRIS_MANUAL,
            'status' => Payment::STATUS_VERIFIED,
            'amount_rupiah' => 15000,
            'paid_at' => CarbonImmutable::parse('2026-04-09 05:10:00')->subMinutes($index),
            'verified_by_user_id' => $admin->id,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('reports.transactions.index', [
            'status' => Invoice::STATUS_PAID,
            'payment_method' => PaymentMethod::QRIS_MANUAL,
            'page' => 2,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Transactions/Index')
            ->where('filters.status', Invoice::STATUS_PAID)
            ->where('filters.payment_method', PaymentMethod::QRIS_MANUAL)
            ->where('ledger.current_page', 2)
            ->where('ledger.total', 16)
            ->has('ledger.data', 1)
        );
});

test('admins can filter transaction ledger by invoice status and payment method', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createTransactionLedgerFixture($admin);

    $this->actingAs($admin)
        ->get(route('reports.transactions.index', [
            'status' => Invoice::STATUS_PAID,
            'payment_method' => PaymentMethod::QRIS_MANUAL,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reports/Transactions/Index')
            ->where('filters.status', Invoice::STATUS_PAID)
            ->where('filters.payment_method', PaymentMethod::QRIS_MANUAL)
            ->has('ledger.data', 1)
            ->where('ledger.data.0.invoiceCode', 'INV-LEDGER-002')
        );
});

test('admins can export filtered transaction ledger as csv', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    createTransactionLedgerFixture($admin);

    $response = $this->actingAs($admin)
        ->get(route('reports.transactions.export', [
            'status' => Invoice::STATUS_PAID,
            'payment_method' => PaymentMethod::QRIS_MANUAL,
        ]));

    $response
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=transaction-ledger.csv');

    $csv = $response->getContent();

    expect($csv)
        ->toContain('invoice_code,status,customer_name,booking_code,session_code,issued_at,closed_at,grand_total_rupiah,verified_paid_rupiah,remaining_balance_rupiah')
        ->toContain('INV-LEDGER-002,paid,"Ledger Customer",,,2026-04-09T08:00:00+00:00,2026-04-09T08:15:00+00:00,66000,66000,0')
        ->not->toContain('INV-LEDGER-001');
});

test('cashiers can not access transaction ledger', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('reports.transactions.index'))
        ->assertForbidden();
});

test('cashiers can not export transaction ledger', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('reports.transactions.export'))
        ->assertForbidden();
});

test('non staff users can not access transaction ledger', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.transactions.index'))
        ->assertForbidden();
});

test('non staff users can not export transaction ledger', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.transactions.export'))
        ->assertForbidden();
});

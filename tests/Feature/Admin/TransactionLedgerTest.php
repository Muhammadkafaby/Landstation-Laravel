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
            ->has('ledger', 2)
            ->where('ledger.0.invoiceCode', 'INV-LEDGER-002')
            ->where('ledger.0.status', Invoice::STATUS_PAID)
            ->where('ledger.0.verifiedPaidRupiah', 66000)
            ->where('ledger.0.remainingBalanceRupiah', 0)
            ->has('ledger.0.lines', 1)
            ->has('ledger.0.payments', 1)
            ->where('ledger.1.invoiceCode', 'INV-LEDGER-001')
            ->where('ledger.1.verifiedPaidRupiah', 20000)
            ->where('ledger.1.remainingBalanceRupiah', 30000)
        );
});

test('cashiers can not access transaction ledger', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('reports.transactions.index'))
        ->assertForbidden();
});

test('non staff users can not access transaction ledger', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.transactions.index'))
        ->assertForbidden();
});

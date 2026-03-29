<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use App\Services\Checkout\InvoiceBuilder;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(ProductCatalogSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-06 12:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('invoice builder creates invoice totals from completed service session and submitted cafe orders', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Invoice Builder Customer',
        'phone' => '081600000001',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-BUILD-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_COMPLETED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-06 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-06 11:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $admin->id,
    ]);
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-BUILD-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-06 10:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-06 11:00:00'),
        'billed_minutes' => 60,
        'pricing_snapshot_json' => [
            'pricing_model' => 'per_interval',
            'billing_interval_minutes' => 30,
            'base_price_rupiah' => 0,
            'price_per_interval_rupiah' => 15000,
            'minimum_charge_rupiah' => 15000,
        ],
        'started_by_user_id' => $admin->id,
        'closed_by_user_id' => $admin->id,
    ]);
    $product = Product::query()->where('sku', 'cafe-americano')->firstOrFail();
    $order = Order::query()->create([
        'order_code' => 'ORD-BUILD-001',
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'service_session_id' => $session->id,
        'status' => Order::STATUS_SUBMITTED,
        'ordered_at' => CarbonImmutable::parse('2026-04-06 10:30:00'),
        'created_by_user_id' => $admin->id,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'qty' => 2,
        'unit_price_rupiah' => 18000,
        'subtotal_rupiah' => 36000,
        'item_snapshot_json' => [
            'sku' => $product->sku,
            'name' => $product->name,
            'product_type' => $product->product_type,
            'price_rupiah' => $product->price_rupiah,
        ],
    ]);

    $invoice = app(InvoiceBuilder::class)->buildForSession($session, $admin);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->status)->toBe(Invoice::STATUS_OPEN)
        ->and($invoice->subtotal_rupiah)->toBe(66000)
        ->and($invoice->grand_total_rupiah)->toBe(66000)
        ->and($invoice->lines)->toHaveCount(2)
        ->and($invoice->lines->pluck('line_type')->all())->toBe(['service_session', 'order_item']);
});

test('invoice builder rejects rebuilding paid invoices', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Paid Invoice Customer',
        'phone' => '081600000002',
    ]);
    $service = Service::query()->where('code', 'rc-adventure')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'rc-01')->firstOrFail();
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-BUILD-002',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'status' => ServiceSession::STATUS_COMPLETED,
        'started_at' => CarbonImmutable::parse('2026-04-06 08:00:00'),
        'ended_at' => CarbonImmutable::parse('2026-04-06 09:00:00'),
        'billed_minutes' => 60,
        'pricing_snapshot_json' => [
            'pricing_model' => 'per_interval',
            'billing_interval_minutes' => 30,
            'base_price_rupiah' => 0,
            'price_per_interval_rupiah' => 25000,
            'minimum_charge_rupiah' => 25000,
        ],
        'started_by_user_id' => $admin->id,
        'closed_by_user_id' => $admin->id,
    ]);

    Invoice::query()->create([
        'invoice_code' => 'INV-PAID-001',
        'customer_id' => $customer->id,
        'service_session_id' => $session->id,
        'status' => Invoice::STATUS_PAID,
        'subtotal_rupiah' => 50000,
        'discount_rupiah' => 0,
        'tax_rupiah' => 0,
        'grand_total_rupiah' => 50000,
        'issued_at' => CarbonImmutable::parse('2026-04-06 09:05:00'),
        'closed_at' => CarbonImmutable::parse('2026-04-06 09:10:00'),
        'created_by_user_id' => $admin->id,
    ]);

    expect(fn () => app(InvoiceBuilder::class)->buildForSession($session, $admin))
        ->toThrow(ValidationException::class);
});

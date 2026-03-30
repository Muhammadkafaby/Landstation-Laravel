<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
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

function createCompletedCheckoutFixture(User $staff, bool $withOrder = true): array
{
    $customer = Customer::query()->create([
        'name' => 'Checkout Customer',
        'phone' => '081700000001',
        'email' => 'checkout@example.com',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();

    $booking = Booking::query()->create([
        'booking_code' => 'BK-CHECKOUT-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_COMPLETED,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-06 10:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-06 11:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);

    $session = ServiceSession::query()->create([
        'session_code' => 'SS-CHECKOUT-001',
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
        'started_by_user_id' => $staff->id,
        'closed_by_user_id' => $staff->id,
    ]);

    if ($withOrder) {
        $product = Product::query()->where('sku', 'cafe-americano')->firstOrFail();
        $order = Order::query()->create([
            'order_code' => 'ORD-CHECKOUT-001',
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'service_session_id' => $session->id,
            'status' => Order::STATUS_SUBMITTED,
            'ordered_at' => CarbonImmutable::parse('2026-04-06 10:30:00'),
            'created_by_user_id' => $staff->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price_rupiah' => $product->price_rupiah,
            'subtotal_rupiah' => $product->price_rupiah * 2,
            'item_snapshot_json' => [
                'sku' => $product->sku,
                'name' => $product->name,
                'product_type' => $product->product_type,
                'price_rupiah' => $product->price_rupiah,
            ],
        ]);
    }

    return [$customer, $booking, $session];
}

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(ProductCatalogSeeder::class);
    $this->seed(PaymentMethodSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-06 14:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('cashiers can access checkout preview for completed sessions', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    [, , $session] = createCompletedCheckoutFixture($cashier);

    $this->actingAs($cashier)
        ->get(route('pos.checkout.show', $session))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos/Checkout/Show')
            ->where('invoice.status', 'open')
            ->where('invoice.grandTotalRupiah', 66000)
            ->where('remainingBalanceRupiah', 66000)
            ->has('invoice.lines', 2)
            ->has('paymentMethods', 2)
        );
});

test('cashiers can submit cash payments from checkout', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    [, , $session] = createCompletedCheckoutFixture($cashier, false);

    $this->actingAs($cashier)
        ->post(route('pos.checkout.payments.store', $session), [
            'payment_method_code' => PaymentMethod::CASH,
            'amount_rupiah' => 30000,
            'reference_number' => 'CASH-CHECKOUT-001',
            'notes' => 'Paid at cashier desk',
        ])
        ->assertRedirect(route('pos.checkout.show', $session));

    $this->assertDatabaseHas('payments', [
        'payment_method_code' => PaymentMethod::CASH,
        'amount_rupiah' => 30000,
        'reference_number' => 'CASH-CHECKOUT-001',
        'verified_by_user_id' => $cashier->id,
    ]);

    $this->assertDatabaseHas('invoices', [
        'service_session_id' => $session->id,
        'status' => 'paid',
        'grand_total_rupiah' => 30000,
    ]);
});

test('checkout rejects overpayment beyond remaining balance', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    [, , $session] = createCompletedCheckoutFixture($cashier, false);

    $this->actingAs($cashier)
        ->from(route('pos.checkout.show', $session))
        ->post(route('pos.checkout.payments.store', $session), [
            'payment_method_code' => PaymentMethod::CASH,
            'amount_rupiah' => 35000,
        ])
        ->assertRedirect(route('pos.checkout.show', $session))
        ->assertSessionHasErrors('amount_rupiah');
});

test('checkout shows paid state after successful qris manual settlement', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    [, , $session] = createCompletedCheckoutFixture($cashier);

    $this->actingAs($cashier)
        ->post(route('pos.checkout.payments.store', $session), [
            'payment_method_code' => PaymentMethod::QRIS_MANUAL,
            'amount_rupiah' => 66000,
            'reference_number' => 'QRIS-CHECKOUT-001',
            'notes' => 'Static QR verified',
        ])
        ->assertRedirect(route('pos.checkout.show', $session));

    $this->actingAs($cashier)
        ->get(route('pos.checkout.show', $session))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos/Checkout/Show')
            ->where('invoice.status', 'paid')
            ->where('remainingBalanceRupiah', 0)
            ->has('payments', 1)
            ->where('payments.0.paymentMethodCode', PaymentMethod::QRIS_MANUAL)
        );
});

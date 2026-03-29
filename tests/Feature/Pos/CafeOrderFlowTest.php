<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(ProductCatalogSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-04-04 15:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

test('cashiers can access the pos cafe order page', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('pos.orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos/Orders/Index')
            ->has('productOptions')
            ->has('activeSessionOptions')
        );
});

test('cashiers can create cafe orders linked to active service sessions', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $customer = Customer::query()->create([
        'name' => 'Cafe Session Customer',
        'phone' => '081400000002',
    ]);
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-ORDER-002',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CHECKED_IN,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-04 14:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-04 16:00:00'),
        'duration_minutes' => 120,
        'created_by_user_id' => $cashier->id,
    ]);
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-ORDER-002',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => CarbonImmutable::parse('2026-04-04 14:00:00'),
        'billed_minutes' => 0,
        'started_by_user_id' => $cashier->id,
    ]);
    $product = Product::query()->where('sku', 'cafe-americano')->firstOrFail();

    $this->actingAs($cashier)
        ->post(route('pos.orders.store'), [
            'service_session_id' => $session->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                    'notes' => 'Tanpa gula',
                ],
            ],
        ])
        ->assertRedirect(route('pos.orders.index'));

    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'service_session_id' => $session->id,
        'status' => 'submitted',
        'created_by_user_id' => $cashier->id,
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'qty' => 2,
        'unit_price_rupiah' => $product->price_rupiah,
        'subtotal_rupiah' => $product->price_rupiah * 2,
    ]);
});

test('cashier can not create cafe orders using inactive products', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);
    $product = Product::query()->where('sku', 'cafe-latte')->firstOrFail();
    $product->update(['is_active' => false]);

    $this->actingAs($cashier)
        ->from(route('pos.orders.index'))
        ->post(route('pos.orders.store'), [
            'customer_name' => 'Walk In Cafe',
            'customer_phone' => '081400000003',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 1,
                ],
            ],
        ])
        ->assertRedirect(route('pos.orders.index'))
        ->assertSessionHasErrors('items.0.product_id');
});

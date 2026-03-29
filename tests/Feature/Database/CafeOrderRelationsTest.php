<?php

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\ProductCatalogSeeder;
use Database\Seeders\ServiceCatalogSeeder;

beforeEach(function () {
    $this->seed(ServiceCatalogSeeder::class);
    $this->seed(ProductCatalogSeeder::class);
});

test('orders can belong to customer booking and service session', function () {
    $customer = Customer::query()->create([
        'name' => 'Cafe Customer',
        'phone' => '081400000001',
    ]);
    $staff = User::factory()->create();
    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'ps-01')->firstOrFail();
    $booking = Booking::query()->create([
        'booking_code' => 'BK-ORDER-001',
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'status' => Booking::STATUS_CHECKED_IN,
        'booking_source' => Booking::SOURCE_ADMIN,
        'start_at' => CarbonImmutable::parse('2026-04-04 12:00:00'),
        'end_at' => CarbonImmutable::parse('2026-04-04 13:00:00'),
        'duration_minutes' => 60,
        'created_by_user_id' => $staff->id,
    ]);
    $session = ServiceSession::query()->create([
        'session_code' => 'SS-ORDER-001',
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'status' => ServiceSession::STATUS_ACTIVE,
        'started_at' => CarbonImmutable::parse('2026-04-04 12:00:00'),
        'billed_minutes' => 0,
        'started_by_user_id' => $staff->id,
    ]);
    $product = Product::query()->where('sku', 'cafe-americano')->firstOrFail();

    $order = Order::query()->create([
        'order_code' => 'ORD-001',
        'customer_id' => $customer->id,
        'booking_id' => $booking->id,
        'service_session_id' => $session->id,
        'status' => Order::STATUS_SUBMITTED,
        'ordered_at' => CarbonImmutable::parse('2026-04-04 12:10:00'),
        'created_by_user_id' => $staff->id,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'qty' => 2,
        'unit_price_rupiah' => 18000,
        'subtotal_rupiah' => 36000,
        'item_snapshot_json' => [
            'sku' => $product->sku,
            'name' => $product->name,
            'price_rupiah' => $product->price_rupiah,
        ],
    ]);

    expect($customer->orders)->toHaveCount(1)
        ->and($booking->orders)->toHaveCount(1)
        ->and($session->orders)->toHaveCount(1)
        ->and($order->items)->toHaveCount(1);
});

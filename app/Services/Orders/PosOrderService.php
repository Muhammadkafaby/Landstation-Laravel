<?php

namespace App\Services\Orders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosOrderService
{
    public function create(array $data, User $createdBy): Order
    {
        return DB::transaction(function () use ($data, $createdBy): Order {
            $serviceSession = isset($data['service_session_id']) && $data['service_session_id'] ? ServiceSession::query()->with('booking.customer')->find($data['service_session_id']) : null;
            $booking = isset($data['booking_id']) && $data['booking_id']
                ? Booking::query()->find($data['booking_id'])
                : $serviceSession?->booking;
            $customer = $this->resolveCustomer($data, $booking, $serviceSession);

            $order = Order::query()->create([
                'order_code' => $this->generateOrderCode(),
                'customer_id' => $customer?->id,
                'booking_id' => $booking?->id,
                'service_session_id' => $serviceSession?->id,
                'status' => Order::STATUS_SUBMITTED,
                'ordered_at' => CarbonImmutable::now(),
                'created_by_user_id' => $createdBy->id,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'unit_price_rupiah' => $product->price_rupiah,
                    'subtotal_rupiah' => $product->price_rupiah * $item['qty'],
                    'item_snapshot_json' => [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'product_type' => $product->product_type,
                        'price_rupiah' => $product->price_rupiah,
                    ],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $order->load('items');
        });
    }

    protected function resolveCustomer(array $data, ?Booking $booking, ?ServiceSession $serviceSession): ?Customer
    {
        if ($serviceSession?->booking?->customer) {
            return $serviceSession->booking->customer;
        }

        if ($serviceSession?->customer) {
            return $serviceSession->customer;
        }

        if ($booking?->customer) {
            return $booking->customer;
        }

        $customer = Customer::query()
            ->when(
                filled($data['customer_phone'] ?? null),
                fn ($query) => $query->where('phone', $data['customer_phone'])
            )
            ->when(
                blank($data['customer_phone'] ?? null) && filled($data['customer_email'] ?? null),
                fn ($query) => $query->where('email', $data['customer_email'])
            )
            ->first();

        if ($customer === null) {
            return Customer::query()->create([
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'],
                'email' => $data['customer_email'] ?? null,
            ]);
        }

        $customer->update([
            'name' => $data['customer_name'],
            'phone' => $data['customer_phone'],
            'email' => $data['customer_email'] ?? null,
        ]);

        return $customer;
    }

    protected function generateOrderCode(): string
    {
        do {
            $orderCode = 'ORD-'.Str::upper(Str::random(10));
        } while (Order::query()->where('order_code', $orderCode)->exists());

        return $orderCode;
    }
}

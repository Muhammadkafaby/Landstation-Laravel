<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\StoreOrderRequest;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceSession;
use App\Services\Orders\PosOrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Pos/Orders/Index', [
            'productOptions' => ProductCategory::query()
                ->where('is_active', true)
                ->with([
                    'products' => fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('name'),
                ])
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get()
                ->map(fn (ProductCategory $category) => [
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'products' => $category->products->map(fn (Product $product) => [
                        'id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'priceRupiah' => $product->price_rupiah,
                    ])->values(),
                ])
                ->values(),
            'activeSessionOptions' => ServiceSession::query()
                ->with(['customer:id,name', 'service:id,name,code', 'unit:id,name,code'])
                ->whereIn('status', [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED])
                ->orderBy('started_at')
                ->get()
                ->map(fn (ServiceSession $serviceSession) => [
                    'id' => $serviceSession->id,
                    'customerName' => $serviceSession->customer?->name,
                    'serviceName' => $serviceSession->service?->name,
                    'serviceCode' => $serviceSession->service?->code,
                    'unitName' => $serviceSession->unit?->name,
                    'unitCode' => $serviceSession->unit?->code,
                ])
                ->values(),
            'bookingOptions' => Booking::query()
                ->with(['customer:id,name', 'service:id,name,code', 'unit:id,name,code'])
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
                ->orderBy('start_at')
                ->get()
                ->map(fn (Booking $booking) => [
                    'id' => $booking->id,
                    'bookingCode' => $booking->booking_code,
                    'customerName' => $booking->customer?->name,
                    'serviceName' => $booking->service?->name,
                    'serviceCode' => $booking->service?->code,
                    'unitName' => $booking->unit?->name,
                    'unitCode' => $booking->unit?->code,
                ])
                ->values(),
            'recentOrders' => Order::query()
                ->with(['customer:id,name', 'createdBy:id,name'])
                ->withCount('items')
                ->orderByDesc('ordered_at')
                ->limit(10)
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'orderCode' => $order->order_code,
                    'customerName' => $order->customer?->name,
                    'status' => $order->status,
                    'itemsCount' => $order->items_count,
                    'orderedAt' => optional($order->ordered_at)->toIso8601String(),
                    'createdByName' => $order->createdBy?->name,
                ])
                ->values(),
        ]);
    }

    public function store(StoreOrderRequest $request, PosOrderService $posOrderService): RedirectResponse
    {
        $posOrderService->create($request->validated(), $request->user());

        return redirect()->route('pos.orders.index');
    }
}

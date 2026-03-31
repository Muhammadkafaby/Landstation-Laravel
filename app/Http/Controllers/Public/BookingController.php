<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Services\Booking\BookingCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function create(Request $request): Response
    {
        $serviceOptions = $this->serviceOptions(true);
        $requestedServiceId = $request->filled('service')
            ? $request->integer('service')
            : null;

        $preferredServiceId = null;

        if (filled($requestedServiceId)) {
            $preferredServiceId = collect($serviceOptions)
                ->pluck('id')
                ->contains($requestedServiceId)
                ? $requestedServiceId
                : null;
        }

        return Inertia::render('Public/Bookings/Create', [
            'serviceOptions' => $serviceOptions,
            'preferredServiceId' => $preferredServiceId,
        ]);
    }

    public function store(StoreBookingRequest $request, BookingCreator $bookingCreator): RedirectResponse
    {
        $bookingCreator->create(
            $request->validated(),
            Booking::SOURCE_PUBLIC,
            Booking::STATUS_HELD,
        );

        return redirect()->route('bookings.create');
    }

    protected function serviceOptions(bool $onlineOnly): array
    {
        return Service::query()
            ->where('is_active', true)
            ->where('service_type', Service::TYPE_TIMED_UNIT)
            ->whereHas('bookingPolicy', function ($query) use ($onlineOnly): void {
                if ($onlineOnly) {
                    $query->where('online_booking_allowed', true);
                }
            })
            ->with([
                'bookingPolicy',
                'units' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('is_bookable', true)
                    ->where('status', 'available')
                    ->orderBy('code'),
            ])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->map(fn (Service $service) => [
                'id' => $service->id,
                'code' => $service->code,
                'name' => $service->name,
                'bookingPolicy' => [
                    'slotIntervalMinutes' => $service->bookingPolicy?->slot_interval_minutes,
                    'minDurationMinutes' => $service->bookingPolicy?->min_duration_minutes,
                    'maxDurationMinutes' => $service->bookingPolicy?->max_duration_minutes,
                ],
                'layout' => [
                    'mode' => $service->layout_mode,
                    'canvasWidth' => $service->layout_canvas_width,
                    'canvasHeight' => $service->layout_canvas_height,
                    'backgroundImagePath' => $service->layout_background_image_path,
                    'meta' => $service->layout_meta_json ?? [],
                ],
                'units' => $service->units->map(fn ($unit) => [
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'zone' => $unit->zone,
                    'status' => $unit->status,
                    'layout' => [
                        'x' => $unit->layout_x,
                        'y' => $unit->layout_y,
                        'w' => $unit->layout_w,
                        'h' => $unit->layout_h,
                        'rotation' => $unit->layout_rotation,
                        'zIndex' => $unit->layout_z_index,
                        'meta' => $unit->layout_meta_json ?? [],
                    ],
                ])->values(),
            ])
            ->values()
            ->all();
    }
}

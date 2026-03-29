<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Services\Booking\BookingCreator;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Public/Bookings/Create', [
            'serviceOptions' => $this->serviceOptions(true),
        ]);
    }

    public function store(StoreBookingRequest $request, BookingCreator $bookingCreator): RedirectResponse
    {
        $bookingCreator->create(
            $request->validated(),
            Booking::SOURCE_PUBLIC,
            Booking::STATUS_PENDING,
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
                'units' => $service->units->map(fn ($unit) => [
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'zone' => $unit->zone,
                ])->values(),
            ])
            ->values()
            ->all();
    }
}

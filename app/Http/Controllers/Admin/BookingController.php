<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookingRequest;
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
        return Inertia::render('Admin/Bookings/Create', [
            'serviceOptions' => $this->serviceOptions(),
        ]);
    }

    public function store(StoreBookingRequest $request, BookingCreator $bookingCreator): RedirectResponse
    {
        $bookingCreator->create(
            $request->validated(),
            Booking::SOURCE_ADMIN,
            Booking::STATUS_CONFIRMED,
            $request->user(),
        );

        return redirect()->route('management.bookings.create');
    }

    protected function serviceOptions(): array
    {
        return Service::query()
            ->where('is_active', true)
            ->where('service_type', Service::TYPE_TIMED_UNIT)
            ->whereHas('bookingPolicy')
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

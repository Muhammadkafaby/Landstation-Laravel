<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransitionBookingStatusRequest;
use App\Models\Booking;
use App\Services\Booking\BookingStatusManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => Booking::query()
                ->with(['customer:id,name,phone', 'service:id,name,code', 'unit:id,name,code'])
                ->withCount('serviceSessions')
                ->orderByDesc('start_at')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Booking $booking) => [
                    'id' => $booking->id,
                    'bookingCode' => $booking->booking_code,
                    'customerName' => $booking->customer?->name,
                    'customerPhone' => $booking->customer?->phone,
                    'serviceName' => $booking->service?->name,
                    'serviceCode' => $booking->service?->code,
                    'unitName' => $booking->unit?->name,
                    'unitCode' => $booking->unit?->code,
                    'status' => $booking->status,
                    'source' => $booking->booking_source,
                    'startAt' => optional($booking->start_at)->toIso8601String(),
                    'endAt' => optional($booking->end_at)->toIso8601String(),
                    'serviceSessionsCount' => $booking->service_sessions_count,
                ]),
            'transitionOptions' => [
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_COMPLETED,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_NO_SHOW,
            ],
        ]);
    }

    public function transition(
        TransitionBookingStatusRequest $request,
        Booking $booking,
        BookingStatusManager $bookingStatusManager,
    ): RedirectResponse {
        $bookingStatusManager->transition($booking, $request->validated('status'));

        return redirect()->route('management.bookings.index');
    }
}

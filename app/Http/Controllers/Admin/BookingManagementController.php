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
        $serverNow = now();

        return Inertia::render('Admin/Bookings/Index', [
            'serverNow' => $serverNow->toIso8601String(),
            'heldQueue' => Booking::query()
                ->with(['customer:id,name,phone', 'service:id,name,code', 'unit:id,name,code'])
                ->where('status', Booking::STATUS_HELD)
                ->whereNotNull('hold_expires_at')
                ->where('hold_expires_at', '>', $serverNow)
                ->orderBy('hold_expires_at')
                ->limit(8)
                ->get()
                ->map(fn (Booking $booking) => [
                    'id' => $booking->id,
                    'bookingCode' => $booking->booking_code,
                    'customerName' => $booking->customer?->name,
                    'customerPhone' => $booking->customer?->phone,
                    'serviceName' => $booking->service?->name,
                    'serviceCode' => $booking->service?->code,
                    'unitName' => $booking->unit?->name,
                    'unitCode' => $booking->unit?->code,
                    'status' => $booking->runtimeStatus(),
                    'holdExpiresAt' => optional($booking->hold_expires_at)->toIso8601String(),
                    'remainingSeconds' => max(0, $serverNow->diffInSeconds($booking->hold_expires_at, false)),
                    'source' => $booking->booking_source,
                    'startAt' => optional($booking->start_at)->toIso8601String(),
                    'endAt' => optional($booking->end_at)->toIso8601String(),
                ])
                ->values(),
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
                    'status' => $booking->runtimeStatus(),
                    'holdExpiresAt' => optional($booking->hold_expires_at)->toIso8601String(),
                    'confirmedAt' => optional($booking->confirmed_at)->toIso8601String(),
                    'expiredAt' => optional($booking->expired_at)->toIso8601String(),
                    'source' => $booking->booking_source,
                    'startAt' => optional($booking->start_at)->toIso8601String(),
                    'endAt' => optional($booking->end_at)->toIso8601String(),
                    'serviceSessionsCount' => $booking->service_sessions_count,
                ]),
            'transitionOptions' => [
                Booking::STATUS_HELD,
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_COMPLETED,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_NO_SHOW,
                Booking::STATUS_EXPIRED,
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

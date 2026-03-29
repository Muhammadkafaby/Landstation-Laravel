<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\StartServiceSessionRequest;
use App\Http\Requests\Pos\StopServiceSessionRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Services\Sessions\ServiceSessionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(): Response
    {
        $blockedUnitIds = ServiceSession::query()
            ->whereIn('status', [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED])
            ->pluck('service_unit_id')
            ->filter()
            ->values();

        return Inertia::render('Pos/Sessions/Index', [
            'serviceOptions' => Service::query()
                ->where('is_active', true)
                ->where('service_type', Service::TYPE_TIMED_UNIT)
                ->with([
                    'units' => fn ($query) => $query
                        ->where('is_active', true)
                        ->where('is_bookable', true)
                        ->where('status', ServiceUnit::STATUS_AVAILABLE)
                        ->when($blockedUnitIds->isNotEmpty(), fn ($unitQuery) => $unitQuery->whereNotIn('id', $blockedUnitIds))
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
                ->values(),
            'bookingOptions' => Booking::query()
                ->with(['customer:id,name', 'service:id,name,code', 'unit:id,name,code'])
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
                ->whereDoesntHave('serviceSessions', fn ($query) => $query->whereIn('status', [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED]))
                ->orderBy('start_at')
                ->get()
                ->map(fn (Booking $booking) => [
                    'id' => $booking->id,
                    'bookingCode' => $booking->booking_code,
                    'customerName' => $booking->customer?->name,
                    'serviceId' => $booking->service_id,
                    'serviceName' => $booking->service?->name,
                    'serviceCode' => $booking->service?->code,
                    'unitId' => $booking->service_unit_id,
                    'unitName' => $booking->unit?->name,
                    'unitCode' => $booking->unit?->code,
                ])
                ->values(),
            'activeSessions' => ServiceSession::query()
                ->with(['customer:id,name,phone', 'service:id,name,code', 'unit:id,name,code', 'booking:id,booking_code'])
                ->whereIn('status', [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED])
                ->orderBy('started_at')
                ->get()
                ->map(fn (ServiceSession $serviceSession) => [
                    'id' => $serviceSession->id,
                    'sessionCode' => $serviceSession->session_code,
                    'customerName' => $serviceSession->customer?->name,
                    'customerPhone' => $serviceSession->customer?->phone,
                    'serviceName' => $serviceSession->service?->name,
                    'serviceCode' => $serviceSession->service?->code,
                    'unitName' => $serviceSession->unit?->name,
                    'unitCode' => $serviceSession->unit?->code,
                    'bookingCode' => $serviceSession->booking?->booking_code,
                    'status' => $serviceSession->status,
                    'startedAt' => optional($serviceSession->started_at)->toIso8601String(),
                ])
                ->values(),
        ]);
    }

    public function store(StartServiceSessionRequest $request, ServiceSessionService $serviceSessionService): RedirectResponse
    {
        $serviceSessionService->start($request->validated(), $request->user());

        return redirect()->route('pos.sessions.index');
    }

    public function stop(StopServiceSessionRequest $request, ServiceSession $serviceSession, ServiceSessionService $serviceSessionService): RedirectResponse
    {
        $serviceSessionService->stop($serviceSession, $request->user());

        return redirect()->route('pos.sessions.index');
    }
}

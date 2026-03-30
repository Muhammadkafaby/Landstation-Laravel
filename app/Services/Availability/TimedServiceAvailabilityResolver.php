<?php

namespace App\Services\Availability;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class TimedServiceAvailabilityResolver
{
    public function assertBookableWindow(Service $service, CarbonInterface $startAt, CarbonInterface $endAt): void
    {
        if ($service->service_type !== Service::TYPE_TIMED_UNIT) {
            throw ValidationException::withMessages([
                'service_id' => 'Only timed-unit services can use the timed availability resolver.',
            ]);
        }

        $policy = $service->bookingPolicy;

        if ($policy === null) {
            throw ValidationException::withMessages([
                'service_id' => 'The selected service does not have a booking policy.',
            ]);
        }

        if ($endAt->lessThanOrEqualTo($startAt)) {
            throw ValidationException::withMessages([
                'end_at' => 'The booking end time must be after the start time.',
            ]);
        }

        $durationMinutes = $startAt->diffInMinutes($endAt);

        if ($startAt->lessThan(now()->addMinutes($policy->lead_time_minutes))) {
            throw ValidationException::withMessages([
                'start_at' => 'The booking start time does not meet the lead time requirement.',
            ]);
        }

        if ($startAt->greaterThan(now()->addDays($policy->max_advance_days))) {
            throw ValidationException::withMessages([
                'start_at' => 'The booking start time exceeds the max advance window.',
            ]);
        }

        if ($durationMinutes < $policy->min_duration_minutes) {
            throw ValidationException::withMessages([
                'end_at' => 'The booking duration is shorter than the minimum duration.',
            ]);
        }

        if ($policy->max_duration_minutes !== null && $durationMinutes > $policy->max_duration_minutes) {
            throw ValidationException::withMessages([
                'end_at' => 'The booking duration exceeds the maximum duration.',
            ]);
        }

        if ($durationMinutes % $policy->slot_interval_minutes !== 0) {
            throw ValidationException::withMessages([
                'end_at' => 'The booking duration must align with the configured slot interval.',
            ]);
        }
    }

    public function availableUnits(Service $service, CarbonInterface $startAt, CarbonInterface $endAt, ?int $excludeBookingId = null): Collection
    {
        $this->assertBookableWindow($service, $startAt, $endAt);

        $blockingSessionStatuses = [
            ServiceSession::STATUS_ACTIVE,
            ServiceSession::STATUS_PAUSED,
        ];

        $blockedByBookings = Booking::query()
            ->where('service_id', $service->id)
            ->whereNotNull('service_unit_id')
            ->where(function ($query): void {
                $query->where(function ($heldQuery): void {
                    $heldQuery->where('status', Booking::STATUS_HELD)
                        ->whereNotNull('hold_expires_at')
                        ->where('hold_expires_at', '>', now());
                })->orWhereIn('status', [
                    Booking::STATUS_PENDING,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CHECKED_IN,
                ]);
            })
            ->when($excludeBookingId !== null, fn ($query) => $query->whereKeyNot($excludeBookingId))
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->pluck('service_unit_id');

        $blockedBySessions = ServiceSession::query()
            ->where('service_id', $service->id)
            ->whereNotNull('service_unit_id')
            ->whereIn('status', $blockingSessionStatuses)
            ->where('started_at', '<', $endAt)
            ->where(function ($query) use ($startAt): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $startAt);
            })
            ->pluck('service_unit_id');

        $blockedUnitIds = $blockedByBookings
            ->merge($blockedBySessions)
            ->unique()
            ->values();

        return ServiceUnit::query()
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->where('is_bookable', true)
            ->where('status', ServiceUnit::STATUS_AVAILABLE)
            ->when($blockedUnitIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $blockedUnitIds))
            ->orderBy('code')
            ->get();
    }
}

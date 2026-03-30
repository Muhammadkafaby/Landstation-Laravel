<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingStatusManager
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function transition(Booking $booking, string $targetStatus, ?User $actor = null): Booking
    {
        $allowedTransitions = [
            Booking::STATUS_HELD => [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_EXPIRED,
            ],
            Booking::STATUS_PENDING => [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CANCELLED,
            ],
            Booking::STATUS_CONFIRMED => [
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_NO_SHOW,
            ],
            Booking::STATUS_CHECKED_IN => [
                Booking::STATUS_COMPLETED,
            ],
            Booking::STATUS_COMPLETED => [],
            Booking::STATUS_CANCELLED => [],
            Booking::STATUS_NO_SHOW => [],
            Booking::STATUS_EXPIRED => [],
        ];

        if ($booking->holdHasExpired()) {
            $fromStatus = $booking->status;

            $booking->update([
                'status' => Booking::STATUS_EXPIRED,
                'expired_at' => $booking->expired_at ?? CarbonImmutable::now(),
            ]);

            if ($targetStatus === Booking::STATUS_EXPIRED) {
                $this->auditLogger->log($actor, 'booking.status_transitioned', $booking, [
                    'from_status' => $fromStatus,
                    'to_status' => Booking::STATUS_EXPIRED,
                ]);

                return $booking->refresh();
            }

            if ($targetStatus !== Booking::STATUS_EXPIRED) {
                throw ValidationException::withMessages([
                    'status' => 'The requested booking hold has already expired.',
                ]);
            }
        }

        if (! in_array($targetStatus, $allowedTransitions[$booking->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => 'The requested booking status transition is not allowed.',
            ]);
        }

        $fromStatus = $booking->status;

        $booking->update([
            'status' => $targetStatus,
            'confirmed_at' => $targetStatus === Booking::STATUS_CONFIRMED
                ? ($booking->confirmed_at ?? CarbonImmutable::now())
                : $booking->confirmed_at,
            'expired_at' => $targetStatus === Booking::STATUS_EXPIRED
                ? ($booking->expired_at ?? CarbonImmutable::now())
                : $booking->expired_at,
        ]);

        $this->auditLogger->log($actor, 'booking.status_transitioned', $booking, [
            'from_status' => $fromStatus,
            'to_status' => $targetStatus,
        ]);

        return $booking->refresh();
    }
}

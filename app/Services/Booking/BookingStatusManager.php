<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class BookingStatusManager
{
    public function transition(Booking $booking, string $targetStatus): Booking
    {
        $allowedTransitions = [
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
        ];

        if (! in_array($targetStatus, $allowedTransitions[$booking->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => 'The requested booking status transition is not allowed.',
            ]);
        }

        $booking->update([
            'status' => $targetStatus,
        ]);

        return $booking->refresh();
    }
}

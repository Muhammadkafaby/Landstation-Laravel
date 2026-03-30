<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\Booking\BookingStatusManager;
use Illuminate\Console\Command;

class ExpireHeldBookingsCommand extends Command
{
    protected $signature = 'bookings:expire-held';

    protected $description = 'Expire held bookings whose hold window has elapsed';

    public function handle(BookingStatusManager $bookingStatusManager): int
    {
        $expiredCount = 0;

        Booking::query()
            ->where('status', Booking::STATUS_HELD)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', now())
            ->lazyById()
            ->each(function (Booking $booking) use ($bookingStatusManager, &$expiredCount): void {
                $bookingStatusManager->transition($booking, Booking::STATUS_EXPIRED);
                $expiredCount++;
            });

        $this->info("Expired {$expiredCount} held booking(s).");

        return self::SUCCESS;
    }
}

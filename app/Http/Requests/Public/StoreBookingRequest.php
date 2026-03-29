<?php

namespace App\Http\Requests\Public;

use App\Http\Requests\Booking\BaseStoreBookingRequest;

class StoreBookingRequest extends BaseStoreBookingRequest
{
    protected function requiresOnlineBooking(): bool
    {
        return true;
    }
}

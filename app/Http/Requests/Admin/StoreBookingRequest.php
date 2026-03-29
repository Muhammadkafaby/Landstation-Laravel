<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Booking\BaseStoreBookingRequest;

class StoreBookingRequest extends BaseStoreBookingRequest
{
    protected function requiresOnlineBooking(): bool
    {
        return false;
    }
}

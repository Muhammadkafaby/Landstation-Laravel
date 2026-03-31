<?php

namespace App\Http\Requests\Public;

use App\Http\Requests\Booking\BaseStoreBookingRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends BaseStoreBookingRequest
{
    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startAt = CarbonImmutable::parse($this->input('start_at'));
            $endAt = CarbonImmutable::parse($this->input('end_at'));
            $todayStart = now()->startOfDay();
            $tomorrowEnd = now()->addDay()->endOfDay();
            $openingHour = 9;
            $latestEndHour = 23;

            if ($startAt->lessThan($todayStart) || $startAt->greaterThan($tomorrowEnd)) {
                $validator->errors()->add('start_at', 'Waktu mulai hanya boleh pada hari ini atau besok.');

                return;
            }

            if ($endAt->lessThan($todayStart) || $endAt->greaterThan($tomorrowEnd)) {
                $validator->errors()->add('end_at', 'Waktu selesai hanya boleh pada hari ini atau besok.');

                return;
            }

            $openingAt = $startAt->startOfDay()->addHours($openingHour);
            $latestEndAt = $startAt->startOfDay()->addHours($latestEndHour);

            if ($startAt->lessThan($openingAt)) {
                $validator->errors()->add('start_at', 'Waktu mulai booking minimal pukul 09:00.');

                return;
            }

            if ($endAt->greaterThan($latestEndAt)) {
                $validator->errors()->add('end_at', 'Waktu selesai booking maksimal pukul 23:00.');
            }
        });
    }

    protected function requiresOnlineBooking(): bool
    {
        return true;
    }
}

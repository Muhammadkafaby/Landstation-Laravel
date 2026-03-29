<?php

namespace App\Http\Requests\Pos;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StartServiceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'service_id' => ['required', 'exists:services,id'],
            'service_unit_id' => ['required', 'exists:service_units,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $service = Service::query()
                ->whereKey($this->integer('service_id'))
                ->where('is_active', true)
                ->first();

            if ($service === null || $service->service_type !== Service::TYPE_TIMED_UNIT) {
                $validator->errors()->add('service_id', 'The selected service can not start a timed session.');

                return;
            }

            $unit = ServiceUnit::query()
                ->whereKey($this->integer('service_unit_id'))
                ->where('service_id', $service->id)
                ->first();

            if ($unit === null) {
                $validator->errors()->add('service_unit_id', 'The selected unit does not belong to the selected service.');

                return;
            }

            if (! $unit->is_active || ! $unit->is_bookable || $unit->status !== ServiceUnit::STATUS_AVAILABLE) {
                $validator->errors()->add('service_unit_id', 'The selected unit is not ready for a timed session.');

                return;
            }

            $hasBlockingSession = ServiceSession::query()
                ->where('service_unit_id', $unit->id)
                ->whereIn('status', [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED])
                ->exists();

            if ($hasBlockingSession) {
                $validator->errors()->add('service_unit_id', 'The selected unit already has an active service session.');

                return;
            }

            if ($this->filled('booking_id')) {
                $booking = Booking::query()->whereKey($this->integer('booking_id'))->first();

                if ($booking === null) {
                    $validator->errors()->add('booking_id', 'The selected booking was not found.');

                    return;
                }

                if (! in_array($booking->status, [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN], true)) {
                    $validator->errors()->add('booking_id', 'The selected booking is not ready to start a service session.');
                }

                if ($booking->service_id !== $service->id || $booking->service_unit_id !== $unit->id) {
                    $validator->errors()->add('booking_id', 'The selected booking must match the selected service and unit.');
                }
            } elseif (! $this->filled('customer_name') || ! $this->filled('customer_phone')) {
                $validator->errors()->add('customer_name', 'Customer name is required for walk-in sessions.');
                $validator->errors()->add('customer_phone', 'Customer phone is required for walk-in sessions.');
            }
        });
    }
}

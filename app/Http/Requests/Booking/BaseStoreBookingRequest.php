<?php

namespace App\Http\Requests\Booking;

use App\Models\Booking;
use App\Models\Service;
use App\Services\Availability\TimedServiceAvailabilityResolver;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

abstract class BaseStoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'service_id' => ['required', 'exists:services,id'],
            'service_unit_id' => ['nullable', 'exists:service_units,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $service = Service::query()
                ->with(['bookingPolicy', 'units'])
                ->whereKey($this->integer('service_id'))
                ->where('is_active', true)
                ->first();

            if ($service === null || $service->service_type !== Service::TYPE_TIMED_UNIT) {
                $validator->errors()->add('service_id', 'The selected service can not be booked.');

                return;
            }

            if ($service->bookingPolicy === null) {
                $validator->errors()->add('service_id', 'The selected service does not have a booking policy.');

                return;
            }

            if ($this->requiresOnlineBooking() && ! $service->bookingPolicy->online_booking_allowed) {
                $validator->errors()->add('service_id', 'The selected service is not available for online booking.');

                return;
            }

            $resolver = app(TimedServiceAvailabilityResolver::class);

            try {
                $startAt = CarbonImmutable::parse($this->input('start_at'));
                $endAt = CarbonImmutable::parse($this->input('end_at'));

                $resolver->assertBookableWindow($service, $startAt, $endAt);
            } catch (\Throwable $throwable) {
                if ($throwable instanceof ValidationException) {
                    foreach ($throwable->errors() as $field => $messages) {
                        foreach ($messages as $message) {
                            $validator->errors()->add($field, $message);
                        }
                    }

                    return;
                }

                throw $throwable;
            }

            $availableUnits = $resolver->availableUnits($service, $startAt, $endAt);

            if ($service->bookingPolicy->requires_unit_assignment && ! $this->filled('service_unit_id')) {
                $validator->errors()->add('service_unit_id', 'The selected service requires a unit assignment.');

                return;
            }

            if ($this->filled('service_unit_id') && ! $availableUnits->contains('id', $this->integer('service_unit_id'))) {
                $validator->errors()->add('service_unit_id', 'The selected unit is not available for that booking window.');

                return;
            }

            $activeHoldCount = Booking::query()
                ->where('status', Booking::STATUS_HELD)
                ->whereNotNull('hold_expires_at')
                ->where('hold_expires_at', '>', now())
                ->whereHas('customer', function ($query): void {
                    $query->where('phone', $this->string('customer_phone')->toString());

                    if ($this->filled('customer_email')) {
                        $query->orWhere('email', $this->string('customer_email')->toString());
                    }
                })
                ->count();

            if ($activeHoldCount >= 2) {
                $validator->errors()->add('customer_phone', 'The customer already has the maximum number of active booking holds.');
            }
        });
    }

    abstract protected function requiresOnlineBooking(): bool;
}

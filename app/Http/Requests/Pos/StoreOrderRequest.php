<?php

namespace App\Http\Requests\Pos;

use App\Models\Booking;
use App\Models\Product;
use App\Models\ServiceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'service_session_id' => ['nullable', 'exists:service_sessions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $session = null;

            if ($this->filled('service_session_id')) {
                $session = ServiceSession::query()->with('booking')->find($this->integer('service_session_id'));

                if ($session === null || ! in_array($session->status, [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED], true)) {
                    $validator->errors()->add('service_session_id', 'The selected service session is not available for new orders.');
                }
            }

            if ($this->filled('booking_id')) {
                $booking = Booking::query()->find($this->integer('booking_id'));

                if ($booking === null || ! in_array($booking->status, [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN], true)) {
                    $validator->errors()->add('booking_id', 'The selected booking is not available for new orders.');
                }

                if ($session !== null && $session->booking_id !== null && $session->booking_id !== $booking?->id) {
                    $validator->errors()->add('booking_id', 'The booking must match the selected service session.');
                }
            }

            foreach ($this->input('items', []) as $index => $item) {
                $product = Product::query()->find($item['product_id']);

                if ($product === null || ! $product->is_active) {
                    $validator->errors()->add("items.$index.product_id", 'The selected product is not active.');
                }
            }

            if (! $this->filled('service_session_id') && ! $this->filled('booking_id')) {
                if (! $this->filled('customer_name')) {
                    $validator->errors()->add('customer_name', 'Customer name is required when there is no linked booking or session.');
                }

                if (! $this->filled('customer_phone')) {
                    $validator->errors()->add('customer_phone', 'Customer phone is required when there is no linked booking or session.');
                }
            }
        });
    }
}

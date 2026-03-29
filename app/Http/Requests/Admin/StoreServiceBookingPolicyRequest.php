<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceBookingPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('service_type', Service::TYPE_TIMED_UNIT)),
                'unique:service_booking_policies,service_id',
            ],
            'slot_interval_minutes' => ['required', 'integer', 'min:1'],
            'min_duration_minutes' => ['required', 'integer', 'min:1'],
            'max_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'lead_time_minutes' => ['required', 'integer', 'min:0'],
            'max_advance_days' => ['required', 'integer', 'min:0'],
            'requires_unit_assignment' => ['required', 'boolean'],
            'walk_in_allowed' => ['required', 'boolean'],
            'online_booking_allowed' => ['required', 'boolean'],
        ];
    }
}

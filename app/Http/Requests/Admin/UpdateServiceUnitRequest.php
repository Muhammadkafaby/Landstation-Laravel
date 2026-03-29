<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use App\Models\ServiceUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ServiceUnit $serviceUnit */
        $serviceUnit = $this->route('serviceUnit');

        return [
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('service_type', Service::TYPE_TIMED_UNIT)),
            ],
            'code' => ['required', 'string', 'max:255', Rule::unique('service_units', 'code')->ignore($serviceUnit->id)],
            'name' => ['required', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([
                ServiceUnit::STATUS_AVAILABLE,
                ServiceUnit::STATUS_OCCUPIED,
                ServiceUnit::STATUS_RESERVED,
                ServiceUnit::STATUS_MAINTENANCE,
                ServiceUnit::STATUS_INACTIVE,
            ])],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_bookable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

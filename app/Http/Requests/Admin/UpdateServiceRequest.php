<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Service $service */
        $service = $this->route('service');

        return [
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique('services', 'code')->ignore($service->id)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($service->id)],
            'service_type' => ['required', Rule::in([Service::TYPE_TIMED_UNIT, Service::TYPE_MENU_ONLY])],
            'billing_type' => ['required', Rule::in([Service::BILLING_PER_MINUTE, Service::BILLING_FLAT])],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

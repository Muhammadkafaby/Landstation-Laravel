<?php

namespace App\Http\Requests\Admin;

use App\Models\ServicePricingRule;
use App\Models\ServiceUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'service_unit_id' => [
                'nullable',
                'exists:service_units,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $belongsToService = ServiceUnit::query()
                        ->whereKey($value)
                        ->where('service_id', $this->integer('service_id'))
                        ->exists();

                    if (! $belongsToService) {
                        $fail('The selected service unit must belong to the selected service.');
                    }
                },
            ],
            'pricing_model' => ['required', Rule::in([ServicePricingRule::MODEL_PER_INTERVAL, ServicePricingRule::MODEL_FLAT])],
            'billing_interval_minutes' => ['nullable', 'integer', 'min:1', Rule::requiredIf($this->input('pricing_model') === ServicePricingRule::MODEL_PER_INTERVAL)],
            'base_price_rupiah' => ['required', 'integer', 'min:0'],
            'price_per_interval_rupiah' => ['nullable', 'integer', 'min:0'],
            'minimum_charge_rupiah' => ['nullable', 'integer', 'min:0'],
            'priority' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

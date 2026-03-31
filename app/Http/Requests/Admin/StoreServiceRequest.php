<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'code' => ['required', 'string', 'max:255', 'unique:services,code'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:services,slug'],
            'service_type' => ['required', Rule::in([Service::TYPE_TIMED_UNIT, Service::TYPE_MENU_ONLY])],
            'billing_type' => ['required', Rule::in([Service::BILLING_PER_MINUTE, Service::BILLING_FLAT])],
            'layout_mode' => ['nullable', 'string', 'max:100'],
            'layout_canvas_width' => ['nullable', 'integer', 'min:1'],
            'layout_canvas_height' => ['nullable', 'integer', 'min:1'],
            'layout_background_image_path' => ['nullable', 'string', 'max:255'],
            'layout_meta_json' => ['nullable', 'array'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

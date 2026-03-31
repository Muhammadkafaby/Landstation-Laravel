<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceBookingPolicy;
use App\Models\ServiceCategory;
use App\Models\ServicePricingRule;
use App\Models\ServiceUnit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Services/Index', [
            'categories' => ServiceCategory::query()
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'description', 'is_active'])
                ->map(fn (ServiceCategory $category) => [
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'description' => $category->description,
                    'isActive' => $category->is_active,
                ])
                ->values(),
            'services' => Service::query()
                ->with('category:id,name,code')
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get([
                    'id',
                    'service_category_id',
                    'code',
                    'name',
                    'slug',
                    'service_type',
                    'billing_type',
                    'layout_mode',
                    'layout_canvas_width',
                    'layout_canvas_height',
                    'layout_background_image_path',
                    'layout_meta_json',
                    'is_active',
                    'sort_order',
                ])
                ->map(fn (Service $service) => [
                    'id' => $service->id,
                    'serviceCategoryId' => $service->service_category_id,
                    'categoryName' => $service->category?->name,
                    'categoryCode' => $service->category?->code,
                    'code' => $service->code,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'serviceType' => $service->service_type,
                    'billingType' => $service->billing_type,
                    'layoutMode' => $service->layout_mode,
                    'layoutCanvasWidth' => $service->layout_canvas_width,
                    'layoutCanvasHeight' => $service->layout_canvas_height,
                    'layoutBackgroundImagePath' => $service->layout_background_image_path,
                    'layoutMeta' => $service->layout_meta_json ?? [],
                    'isActive' => $service->is_active,
                    'sortOrder' => $service->sort_order,
                ])
                ->values(),
            'units' => ServiceUnit::query()
                ->with('service:id,name,code,service_type')
                ->orderBy('code')
                ->get([
                    'id',
                    'service_id',
                    'code',
                    'name',
                    'zone',
                    'status',
                    'capacity',
                    'layout_x',
                    'layout_y',
                    'layout_w',
                    'layout_h',
                    'layout_rotation',
                    'layout_z_index',
                    'layout_meta_json',
                    'is_bookable',
                    'is_active',
                ])
                ->map(fn (ServiceUnit $unit) => [
                    'id' => $unit->id,
                    'serviceId' => $unit->service_id,
                    'serviceName' => $unit->service?->name,
                    'serviceCode' => $unit->service?->code,
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'zone' => $unit->zone,
                    'status' => $unit->status,
                    'capacity' => $unit->capacity,
                    'layoutX' => $unit->layout_x,
                    'layoutY' => $unit->layout_y,
                    'layoutW' => $unit->layout_w,
                    'layoutH' => $unit->layout_h,
                    'layoutRotation' => $unit->layout_rotation,
                    'layoutZIndex' => $unit->layout_z_index,
                    'layoutMeta' => $unit->layout_meta_json ?? [],
                    'isBookable' => $unit->is_bookable,
                    'isActive' => $unit->is_active,
                ])
                ->values(),
            'pricingRules' => ServicePricingRule::query()
                ->with(['service:id,name,code', 'unit:id,name,code'])
                ->orderBy('service_id')
                ->orderBy('priority')
                ->get([
                    'id',
                    'service_id',
                    'service_unit_id',
                    'pricing_model',
                    'billing_interval_minutes',
                    'base_price_rupiah',
                    'price_per_interval_rupiah',
                    'minimum_charge_rupiah',
                    'priority',
                    'is_active',
                ])
                ->map(fn (ServicePricingRule $pricingRule) => [
                    'id' => $pricingRule->id,
                    'serviceId' => $pricingRule->service_id,
                    'serviceUnitId' => $pricingRule->service_unit_id,
                    'serviceName' => $pricingRule->service?->name,
                    'serviceCode' => $pricingRule->service?->code,
                    'unitName' => $pricingRule->unit?->name,
                    'unitCode' => $pricingRule->unit?->code,
                    'pricingModel' => $pricingRule->pricing_model,
                    'billingIntervalMinutes' => $pricingRule->billing_interval_minutes,
                    'basePriceRupiah' => $pricingRule->base_price_rupiah,
                    'pricePerIntervalRupiah' => $pricingRule->price_per_interval_rupiah,
                    'minimumChargeRupiah' => $pricingRule->minimum_charge_rupiah,
                    'priority' => $pricingRule->priority,
                    'isActive' => $pricingRule->is_active,
                ])
                ->values(),
            'bookingPolicies' => ServiceBookingPolicy::query()
                ->with('service:id,name,code')
                ->orderBy('service_id')
                ->get([
                    'id',
                    'service_id',
                    'slot_interval_minutes',
                    'min_duration_minutes',
                    'max_duration_minutes',
                    'lead_time_minutes',
                    'max_advance_days',
                    'requires_unit_assignment',
                    'walk_in_allowed',
                    'online_booking_allowed',
                ])
                ->map(fn (ServiceBookingPolicy $bookingPolicy) => [
                    'id' => $bookingPolicy->id,
                    'serviceId' => $bookingPolicy->service_id,
                    'serviceName' => $bookingPolicy->service?->name,
                    'serviceCode' => $bookingPolicy->service?->code,
                    'slotIntervalMinutes' => $bookingPolicy->slot_interval_minutes,
                    'minDurationMinutes' => $bookingPolicy->min_duration_minutes,
                    'maxDurationMinutes' => $bookingPolicy->max_duration_minutes,
                    'leadTimeMinutes' => $bookingPolicy->lead_time_minutes,
                    'maxAdvanceDays' => $bookingPolicy->max_advance_days,
                    'requiresUnitAssignment' => $bookingPolicy->requires_unit_assignment,
                    'walkInAllowed' => $bookingPolicy->walk_in_allowed,
                    'onlineBookingAllowed' => $bookingPolicy->online_booking_allowed,
                ])
                ->values(),
            'options' => [
                'serviceTypes' => [
                    ['value' => Service::TYPE_TIMED_UNIT, 'label' => 'Timed Unit'],
                    ['value' => Service::TYPE_MENU_ONLY, 'label' => 'Menu Only'],
                ],
                'billingTypes' => [
                    ['value' => Service::BILLING_PER_MINUTE, 'label' => 'Per Minute'],
                    ['value' => Service::BILLING_FLAT, 'label' => 'Flat'],
                ],
                'unitStatuses' => [
                    ['value' => ServiceUnit::STATUS_AVAILABLE, 'label' => 'Available'],
                    ['value' => ServiceUnit::STATUS_OCCUPIED, 'label' => 'Occupied'],
                    ['value' => ServiceUnit::STATUS_RESERVED, 'label' => 'Reserved'],
                    ['value' => ServiceUnit::STATUS_MAINTENANCE, 'label' => 'Maintenance'],
                    ['value' => ServiceUnit::STATUS_INACTIVE, 'label' => 'Inactive'],
                ],
                'unitServices' => Service::query()
                    ->where('service_type', Service::TYPE_TIMED_UNIT)
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->get(['id', 'name', 'code'])
                    ->map(fn (Service $service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'code' => $service->code,
                    ])
                    ->values(),
                'pricingModels' => [
                    ['value' => ServicePricingRule::MODEL_PER_INTERVAL, 'label' => 'Per Interval'],
                    ['value' => ServicePricingRule::MODEL_FLAT, 'label' => 'Flat'],
                ],
                'pricingServices' => Service::query()
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->get(['id', 'name', 'code'])
                    ->map(fn (Service $service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'code' => $service->code,
                    ])
                    ->values(),
                'bookingPolicyServices' => Service::query()
                    ->where('service_type', Service::TYPE_TIMED_UNIT)
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->get(['id', 'name', 'code'])
                    ->map(fn (Service $service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'code' => $service->code,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::query()->create($request->validated());

        return redirect()->route('management.services.index');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('management.services.index');
    }
}

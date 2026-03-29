<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceBookingPolicy;
use App\Models\ServiceCategory;
use App\Models\ServicePricingRule;
use App\Models\ServiceUnit;
use Inertia\Inertia;
use Inertia\Response;

class ManagementController extends Controller
{
    public function __invoke(): Response
    {
        $categories = ServiceCategory::query()
            ->orderBy('code')
            ->with([
                'services' => fn ($query) => $query
                    ->orderBy('code')
                    ->withCount(['units', 'pricingRules', 'bookingPolicy']),
            ])
            ->get()
            ->map(function (ServiceCategory $category): array {
                $services = $category->services->map(fn (Service $service): array => [
                    'code' => $service->code,
                    'name' => $service->name,
                    'serviceType' => $service->service_type,
                    'billingType' => $service->billing_type,
                    'isActive' => $service->is_active,
                    'units_count' => $service->units_count,
                    'pricing_rules_count' => $service->pricing_rules_count,
                    'has_booking_policy' => $service->booking_policy_count > 0,
                ])->values();

                return [
                    'code' => $category->code,
                    'name' => $category->name,
                    'description' => $category->description,
                    'isActive' => $category->is_active,
                    'services_count' => $services->count(),
                    'units_count' => $services->sum('units_count'),
                    'pricing_rules_count' => $services->sum('pricing_rules_count'),
                    'booking_policies_count' => $services->where('has_booking_policy', true)->count(),
                    'services' => $services,
                ];
            })
            ->values();

        return Inertia::render('Admin/Management/Index', [
            'summary' => [
                'categories' => ServiceCategory::query()->count(),
                'services' => Service::query()->count(),
                'units' => ServiceUnit::query()->count(),
                'pricingRules' => ServicePricingRule::query()->count(),
                'bookingPolicies' => ServiceBookingPolicy::query()->count(),
            ],
            'categories' => $categories,
        ]);
    }
}

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

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->with([
                'services' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->withCount([
                        'units' => fn ($unitQuery) => $unitQuery->where('is_active', true),
                        'pricingRules' => fn ($pricingQuery) => $pricingQuery->where('is_active', true),
                        'bookingPolicy',
                    ])
                    ->with([
                        'pricingRules' => fn ($pricingQuery) => $pricingQuery
                            ->where('is_active', true)
                            ->orderBy('base_price_rupiah')
                            ->orderBy('price_per_interval_rupiah'),
                    ]),
            ])
            ->get()
            ->map(function (ServiceCategory $category): array {
                $services = $category->services->map(function (Service $service): array {
                    $startingPriceRupiah = $service->pricingRules
                        ->map(function ($pricingRule): int {
                            return (int) ($pricingRule->price_per_interval_rupiah ?? $pricingRule->base_price_rupiah);
                        })
                        ->filter(fn (int $value): bool => $value > 0)
                        ->min();

                    return [
                        'name' => $service->name,
                        'serviceType' => $service->service_type,
                        'billingType' => $service->billing_type,
                        'unitsCount' => $service->units_count,
                        'startingPriceRupiah' => $startingPriceRupiah,
                        'hasBookingPolicy' => $service->booking_policy_count > 0,
                    ];
                })->values();

                return [
                    'code' => $category->code,
                    'name' => $category->name,
                    'servicesCount' => $services->count(),
                    'unitsCount' => $services->sum('unitsCount'),
                    'pricingRulesCount' => $category->services->sum('pricing_rules_count'),
                    'bookingPoliciesCount' => $category->services->where('booking_policy_count', '>', 0)->count(),
                    'featuredService' => $services->first(),
                ];
            })
            ->values();

        return Inertia::render('Admin/Dashboard/Index', [
            'summary' => [
                'categories' => ServiceCategory::query()->where('is_active', true)->count(),
                'services' => Service::query()->where('is_active', true)->count(),
                'timedServices' => Service::query()->where('is_active', true)->where('service_type', Service::TYPE_TIMED_UNIT)->count(),
                'menuServices' => Service::query()->where('is_active', true)->where('service_type', Service::TYPE_MENU_ONLY)->count(),
                'units' => ServiceUnit::query()->where('is_active', true)->count(),
                'bookableUnits' => ServiceUnit::query()->where('is_active', true)->where('is_bookable', true)->count(),
                'pricingRules' => ServicePricingRule::query()->where('is_active', true)->count(),
                'bookingPolicies' => ServiceBookingPolicy::query()->count(),
            ],
            'categories' => $categories,
        ]);
    }
}

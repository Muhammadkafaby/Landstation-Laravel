<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePricingRule;
use App\Models\ServiceUnit;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
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
                    $weekdayPrice = $this->priceForDayType($service, ServicePricingRule::DAY_TYPE_WEEKDAY);
                    $weekendPrice = $this->priceForDayType($service, ServicePricingRule::DAY_TYPE_WEEKEND);
                    $startingPriceRupiah = collect([$weekdayPrice, $weekendPrice])
                        ->filter(fn (?int $value): bool => filled($value) && $value > 0)
                        ->min();

                    return [
                        'slug' => $service->slug,
                        'code' => $service->code,
                        'name' => $service->name,
                        'serviceType' => $service->service_type,
                        'billingType' => $service->billing_type,
                        'unitsCount' => $service->units_count,
                        'hasPricing' => $service->pricing_rules_count > 0,
                        'hasBookingPolicy' => $service->booking_policy_count > 0,
                        'startingPriceRupiah' => $startingPriceRupiah,
                        'weekdayPriceRupiah' => $weekdayPrice,
                        'weekendPriceRupiah' => $weekendPrice,
                    ];
                })->values();

                return [
                    'code' => $category->code,
                    'name' => $category->name,
                    'description' => $category->description,
                    'services_count' => $services->count(),
                    'units_count' => $services->sum('unitsCount'),
                    'services' => $services,
                ];
            })
            ->values();

        return Inertia::render('Public/Services/Index', [
            'summary' => [
                'categories' => ServiceCategory::query()->where('is_active', true)->count(),
                'services' => Service::query()->where('is_active', true)->count(),
                'units' => ServiceUnit::query()->where('is_active', true)->count(),
            ],
            'categories' => $categories,
        ]);
    }

    protected function priceForDayType(Service $service, string $dayType): ?int
    {
        return $service->pricingRules
            ->where('day_type', $dayType)
            ->map(function ($pricingRule): int {
                return (int) ($pricingRule->price_per_interval_rupiah ?? $pricingRule->base_price_rupiah);
            })
            ->filter(fn (int $value): bool => $value > 0)
            ->min();
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceUnit;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
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
                        'slug' => $service->slug,
                        'unitsCount' => $service->units_count,
                        'hasPricing' => $service->pricing_rules_count > 0,
                        'hasBookingPolicy' => $service->booking_policy_count > 0,
                        'startingPriceRupiah' => $startingPriceRupiah,
                    ];
                })->values();

                return [
                    'code' => $category->code,
                    'name' => $category->name,
                    'description' => $category->description,
                    'servicesCount' => $services->count(),
                    'unitsCount' => $services->sum('unitsCount'),
                    'featuredService' => $services->first(),
                ];
            })
            ->values();

        return Inertia::render('Public/Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'summary' => [
                'categories' => ServiceCategory::query()->where('is_active', true)->count(),
                'services' => Service::query()->where('is_active', true)->count(),
                'units' => ServiceUnit::query()->where('is_active', true)->count(),
            ],
            'categories' => $categories,
        ]);
    }
}

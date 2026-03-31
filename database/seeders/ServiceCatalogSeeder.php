<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceBookingPolicy;
use App\Models\ServiceCategory;
use App\Models\ServicePricingRule;
use App\Models\ServiceUnit;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => ServiceCategory::CAFE,
                'name' => 'Cafe',
                'description' => 'Kategori untuk menu dan transaksi cafe.',
            ],
            [
                'code' => ServiceCategory::BILLIARD,
                'name' => 'Billiard',
                'description' => 'Kategori untuk meja billiard dan sesi permainan.',
            ],
            [
                'code' => ServiceCategory::PLAYSTATION,
                'name' => 'PlayStation',
                'description' => 'Kategori untuk unit PlayStation dan sesi timer.',
            ],
            [
                'code' => ServiceCategory::RENTAL_RC,
                'name' => 'Rental RC',
                'description' => 'Kategori untuk unit RC dan operasional rental.',
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ],
            );
        }

        $categoryIds = ServiceCategory::query()->pluck('id', 'code');

        $services = [
            [
                'category_code' => ServiceCategory::PLAYSTATION,
                'code' => 'ps-regular',
                'name' => 'PS-4',
                'slug' => 'ps-4',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 1440,
                'layout_canvas_height' => 760,
                'sort_order' => 10,
            ],
            [
                'category_code' => ServiceCategory::PLAYSTATION,
                'code' => 'ps-5',
                'name' => 'PS-5',
                'slug' => 'ps-5',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 1440,
                'layout_canvas_height' => 760,
                'sort_order' => 11,
            ],
            [
                'category_code' => ServiceCategory::PLAYSTATION,
                'code' => 'simulator-balap',
                'name' => 'Simulator Balap',
                'slug' => 'simulator-balap',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 1440,
                'layout_canvas_height' => 760,
                'sort_order' => 12,
            ],
            [
                'category_code' => ServiceCategory::BILLIARD,
                'code' => 'billiard-regular',
                'name' => 'Bilyard Arena',
                'slug' => 'billiard-regular',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 1200,
                'layout_canvas_height' => 760,
                'sort_order' => 20,
            ],
            [
                'category_code' => ServiceCategory::RENTAL_RC,
                'code' => 'rc-adventure',
                'name' => 'RC Arena',
                'slug' => 'rc-arena',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 1680,
                'layout_canvas_height' => 920,
                'sort_order' => 30,
            ],
            [
                'category_code' => ServiceCategory::CAFE,
                'code' => 'cafe-dine-in',
                'name' => 'Cafe Dine In',
                'slug' => 'cafe-dine-in',
                'service_type' => Service::TYPE_MENU_ONLY,
                'billing_type' => Service::BILLING_FLAT,
                'sort_order' => 40,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'service_category_id' => $categoryIds[$service['category_code']],
                    'name' => $service['name'],
                    'slug' => $service['slug'],
                    'service_type' => $service['service_type'],
                    'billing_type' => $service['billing_type'],
                    'layout_mode' => $service['layout_mode'] ?? null,
                    'layout_canvas_width' => $service['layout_canvas_width'] ?? null,
                    'layout_canvas_height' => $service['layout_canvas_height'] ?? null,
                    'layout_background_image_path' => null,
                    'layout_meta_json' => null,
                    'is_active' => true,
                    'sort_order' => $service['sort_order'],
                ],
            );
        }

        $serviceIds = Service::query()->pluck('id', 'code');

        $units = array_merge(
            $this->gridUnits('ps-regular', 'ps', 'PS-4 Unit', 4, 2, 80, 120, 260, 170, 'PlayStation Zone'),
            $this->gridUnits('ps-5', 'ps5', 'PS-5 Unit', 11, 4, 80, 120, 160, 120, 'PlayStation Zone'),
            $this->gridUnits('simulator-balap', 'sim', 'Simulator Balap', 4, 2, 120, 160, 260, 180, 'Simulator Zone'),
            $this->gridUnits('billiard-regular', 'bill', 'Bilyard Table', 4, 2, 120, 180, 260, 140, 'Billiard Zone'),
            $this->gridUnits('rc-adventure', 'rc', 'RC Arena Unit', 20, 5, 120, 120, 120, 90, 'RC Zone'),
        );

        foreach ($units as $unit) {
            ServiceUnit::query()->updateOrCreate(
                ['code' => $unit['code']],
                [
                    'service_id' => $serviceIds[$unit['service_code']],
                    'name' => $unit['name'],
                    'zone' => $unit['zone'],
                    'status' => ServiceUnit::STATUS_AVAILABLE,
                    'capacity' => $unit['capacity'],
                    'layout_x' => $unit['layout_x'] ?? null,
                    'layout_y' => $unit['layout_y'] ?? null,
                    'layout_w' => $unit['layout_w'] ?? null,
                    'layout_h' => $unit['layout_h'] ?? null,
                    'layout_rotation' => 0,
                    'layout_z_index' => 1,
                    'layout_meta_json' => null,
                    'is_bookable' => true,
                    'is_active' => true,
                ],
            );
        }

        $services = Service::query()->pluck('id', 'code');
        $units = ServiceUnit::query()->pluck('id', 'code');

        $pricingRules = [
            [
                'service_code' => 'ps-regular',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKDAY,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 10000,
                'minimum_charge_rupiah' => 10000,
                'priority' => 10,
            ],
            [
                'service_code' => 'ps-regular',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKEND,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 12000,
                'minimum_charge_rupiah' => 12000,
                'priority' => 10,
            ],
            [
                'service_code' => 'ps-5',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKDAY,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 15000,
                'minimum_charge_rupiah' => 15000,
                'priority' => 10,
            ],
            [
                'service_code' => 'ps-5',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKEND,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 17000,
                'minimum_charge_rupiah' => 17000,
                'priority' => 10,
            ],
            [
                'service_code' => 'simulator-balap',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKDAY,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 20000,
                'minimum_charge_rupiah' => 20000,
                'priority' => 10,
            ],
            [
                'service_code' => 'simulator-balap',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKEND,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 25000,
                'minimum_charge_rupiah' => 25000,
                'priority' => 10,
            ],
            [
                'service_code' => 'billiard-regular',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKDAY,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 35000,
                'minimum_charge_rupiah' => 35000,
                'priority' => 10,
            ],
            [
                'service_code' => 'billiard-regular',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKEND,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 45000,
                'minimum_charge_rupiah' => 45000,
                'priority' => 10,
            ],
            [
                'service_code' => 'rc-adventure',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKDAY,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 30000,
                'minimum_charge_rupiah' => 30000,
                'priority' => 10,
            ],
            [
                'service_code' => 'rc-adventure',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKEND,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 60,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 40000,
                'minimum_charge_rupiah' => 40000,
                'priority' => 10,
            ],
            [
                'service_code' => 'cafe-dine-in',
                'day_type' => ServicePricingRule::DAY_TYPE_WEEKDAY,
                'pricing_model' => ServicePricingRule::MODEL_FLAT,
                'billing_interval_minutes' => null,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => null,
                'minimum_charge_rupiah' => null,
                'priority' => 10,
            ],
        ];

        foreach ($pricingRules as $pricingRule) {
            ServicePricingRule::query()->updateOrCreate(
                [
                    'service_id' => $services[$pricingRule['service_code']],
                    'service_unit_id' => null,
                    'day_type' => $pricingRule['day_type'],
                    'priority' => $pricingRule['priority'],
                ],
                [
                    'pricing_model' => $pricingRule['pricing_model'],
                    'billing_interval_minutes' => $pricingRule['billing_interval_minutes'],
                    'base_price_rupiah' => $pricingRule['base_price_rupiah'],
                    'price_per_interval_rupiah' => $pricingRule['price_per_interval_rupiah'],
                    'minimum_charge_rupiah' => $pricingRule['minimum_charge_rupiah'],
                    'starts_at' => null,
                    'ends_at' => null,
                    'is_active' => true,
                ],
            );
        }

        $bookingPolicies = [
            [
                'service_code' => 'ps-regular',
                'slot_interval_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 240,
                'lead_time_minutes' => 30,
                'max_advance_days' => 14,
                'requires_unit_assignment' => true,
                'walk_in_allowed' => true,
                'online_booking_allowed' => true,
            ],
            [
                'service_code' => 'ps-5',
                'slot_interval_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 240,
                'lead_time_minutes' => 30,
                'max_advance_days' => 14,
                'requires_unit_assignment' => true,
                'walk_in_allowed' => true,
                'online_booking_allowed' => true,
            ],
            [
                'service_code' => 'simulator-balap',
                'slot_interval_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 240,
                'lead_time_minutes' => 30,
                'max_advance_days' => 14,
                'requires_unit_assignment' => true,
                'walk_in_allowed' => true,
                'online_booking_allowed' => true,
            ],
            [
                'service_code' => 'billiard-regular',
                'slot_interval_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 240,
                'lead_time_minutes' => 30,
                'max_advance_days' => 14,
                'requires_unit_assignment' => true,
                'walk_in_allowed' => true,
                'online_booking_allowed' => true,
            ],
            [
                'service_code' => 'rc-adventure',
                'slot_interval_minutes' => 60,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 180,
                'lead_time_minutes' => 30,
                'max_advance_days' => 7,
                'requires_unit_assignment' => true,
                'walk_in_allowed' => true,
                'online_booking_allowed' => true,
            ],
        ];

        foreach ($bookingPolicies as $bookingPolicy) {
            ServiceBookingPolicy::query()->updateOrCreate(
                ['service_id' => $services[$bookingPolicy['service_code']]],
                [
                    'slot_interval_minutes' => $bookingPolicy['slot_interval_minutes'],
                    'min_duration_minutes' => $bookingPolicy['min_duration_minutes'],
                    'max_duration_minutes' => $bookingPolicy['max_duration_minutes'],
                    'lead_time_minutes' => $bookingPolicy['lead_time_minutes'],
                    'max_advance_days' => $bookingPolicy['max_advance_days'],
                    'requires_unit_assignment' => $bookingPolicy['requires_unit_assignment'],
                    'walk_in_allowed' => $bookingPolicy['walk_in_allowed'],
                    'online_booking_allowed' => $bookingPolicy['online_booking_allowed'],
                ],
            );
        }
    }

    protected function gridUnits(
        string $serviceCode,
        string $codePrefix,
        string $namePrefix,
        int $count,
        int $columns,
        int $startX,
        int $startY,
        int $cellWidth,
        int $cellHeight,
        string $zone,
    ): array {
        return collect(range(1, $count))
            ->map(function (int $index) use (
                $serviceCode,
                $codePrefix,
                $namePrefix,
                $columns,
                $startX,
                $startY,
                $cellWidth,
                $cellHeight,
                $zone,
            ): array {
                $zeroPadded = str_pad((string) $index, 2, '0', STR_PAD_LEFT);
                $column = ($index - 1) % $columns;
                $row = intdiv($index - 1, $columns);

                return [
                    'service_code' => $serviceCode,
                    'code' => "{$codePrefix}-{$zeroPadded}",
                    'name' => "{$namePrefix} {$zeroPadded}",
                    'zone' => $zone,
                    'capacity' => 4,
                    'layout_x' => $startX + ($column * ($cellWidth + 28)),
                    'layout_y' => $startY + ($row * ($cellHeight + 28)),
                    'layout_w' => $cellWidth,
                    'layout_h' => $cellHeight,
                ];
            })
            ->all();
    }
}

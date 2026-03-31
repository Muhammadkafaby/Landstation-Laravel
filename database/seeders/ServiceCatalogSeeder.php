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
                'name' => 'PlayStation Regular',
                'slug' => 'playstation-regular',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 960,
                'layout_canvas_height' => 640,
                'sort_order' => 10,
            ],
            [
                'category_code' => ServiceCategory::BILLIARD,
                'code' => 'billiard-regular',
                'name' => 'Billiard Regular',
                'slug' => 'billiard-regular',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 960,
                'layout_canvas_height' => 640,
                'sort_order' => 20,
            ],
            [
                'category_code' => ServiceCategory::RENTAL_RC,
                'code' => 'rc-adventure',
                'name' => 'RC Adventure',
                'slug' => 'rc-adventure',
                'service_type' => Service::TYPE_TIMED_UNIT,
                'billing_type' => Service::BILLING_PER_MINUTE,
                'layout_mode' => 'manual_grid',
                'layout_canvas_width' => 960,
                'layout_canvas_height' => 640,
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

        $units = [
            [
                'service_code' => 'ps-regular',
                'code' => 'ps-01',
                'name' => 'PS Room 01',
                'zone' => 'PlayStation Zone',
                'capacity' => 4,
                'layout_x' => 80,
                'layout_y' => 120,
                'layout_w' => 220,
                'layout_h' => 140,
            ],
            [
                'service_code' => 'ps-regular',
                'code' => 'ps-02',
                'name' => 'PS Room 02',
                'zone' => 'PlayStation Zone',
                'capacity' => 4,
                'layout_x' => 340,
                'layout_y' => 120,
                'layout_w' => 220,
                'layout_h' => 140,
            ],
            [
                'service_code' => 'billiard-regular',
                'code' => 'bill-01',
                'name' => 'Billiard Table 01',
                'zone' => 'Billiard Zone',
                'capacity' => 4,
                'layout_x' => 120,
                'layout_y' => 180,
                'layout_w' => 240,
                'layout_h' => 120,
            ],
            [
                'service_code' => 'billiard-regular',
                'code' => 'bill-02',
                'name' => 'Billiard Table 02',
                'zone' => 'Billiard Zone',
                'capacity' => 4,
                'layout_x' => 420,
                'layout_y' => 180,
                'layout_w' => 240,
                'layout_h' => 120,
            ],
            [
                'service_code' => 'rc-adventure',
                'code' => 'rc-01',
                'name' => 'RC Unit 01',
                'zone' => 'RC Zone',
                'capacity' => 1,
                'layout_x' => 200,
                'layout_y' => 220,
                'layout_w' => 180,
                'layout_h' => 120,
            ],
        ];

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
                'service_unit_code' => null,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 30,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 15000,
                'minimum_charge_rupiah' => 15000,
                'priority' => 10,
            ],
            [
                'service_code' => 'ps-regular',
                'service_unit_code' => 'ps-01',
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 30,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 18000,
                'minimum_charge_rupiah' => 18000,
                'priority' => 20,
            ],
            [
                'service_code' => 'billiard-regular',
                'service_unit_code' => null,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 30,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 20000,
                'minimum_charge_rupiah' => 20000,
                'priority' => 10,
            ],
            [
                'service_code' => 'rc-adventure',
                'service_unit_code' => null,
                'pricing_model' => ServicePricingRule::MODEL_PER_INTERVAL,
                'billing_interval_minutes' => 30,
                'base_price_rupiah' => 0,
                'price_per_interval_rupiah' => 25000,
                'minimum_charge_rupiah' => 25000,
                'priority' => 10,
            ],
            [
                'service_code' => 'cafe-dine-in',
                'service_unit_code' => null,
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
                    'service_unit_id' => $pricingRule['service_unit_code'] ? $units[$pricingRule['service_unit_code']] : null,
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
                'slot_interval_minutes' => 30,
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
                'slot_interval_minutes' => 30,
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
                'slot_interval_minutes' => 30,
                'min_duration_minutes' => 30,
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
}

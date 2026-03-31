<?php

use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceBookingPolicy;
use App\Models\ServiceCategory;
use App\Models\ServicePricingRule;
use App\Models\ServiceUnit;
use App\Models\User;
use Database\Seeders\AccessControlSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AccessControlSeeder::class);
    $this->seed(ServiceCatalogSeeder::class);
});

test('admins can access the service catalog management page', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->get(route('management.services.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Services/Index')
            ->has('categories', 4)
            ->has('services', 6)
            ->has('units', 43)
            ->where('services.0.layoutMode', 'manual_grid')
            ->where('services.0.layoutCanvasWidth', 1440)
            ->where('units.0.layoutX', 120)
            ->has('options.unitStatuses', 5)
            ->has('pricingRules', 11)
            ->has('bookingPolicies', 5)
            ->has('options.pricingModels', 2)
        );
});

test('cashiers can not access the service catalog management page', function () {
    $cashier = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::CASHIER)->value('id'),
    ]);

    $this->actingAs($cashier)
        ->get(route('management.services.index'))
        ->assertForbidden();
});

test('admins can create service categories', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->post(route('management.service-categories.store'), [
            'code' => 'vip-lounge',
            'name' => 'VIP Lounge',
            'description' => 'Layanan premium untuk private room.',
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_categories', [
        'code' => 'vip-lounge',
        'name' => 'VIP Lounge',
        'is_active' => true,
    ]);
});

test('service category create validates duplicate code', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $this->actingAs($admin)
        ->from(route('management.services.index'))
        ->post(route('management.service-categories.store'), [
            'code' => 'cafe',
            'name' => 'Cafe Baru',
            'description' => 'Duplicate code',
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'))
        ->assertSessionHasErrors('code');
});

test('admins can update service categories', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $category = ServiceCategory::query()->where('code', 'rental-rc')->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('management.service-categories.update', $category), [
            'code' => 'rental-rc',
            'name' => 'Rental RC Arena',
            'description' => 'Arena RC dan rental unit.',
            'is_active' => false,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_categories', [
        'id' => $category->id,
        'name' => 'Rental RC Arena',
        'is_active' => false,
    ]);
});

test('admins can create services', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $category = ServiceCategory::query()->where('code', 'playstation')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('management.services.store'), [
            'service_category_id' => $category->id,
            'code' => 'ps-vip',
            'name' => 'PlayStation VIP',
            'slug' => 'playstation-vip',
            'service_type' => Service::TYPE_TIMED_UNIT,
            'billing_type' => Service::BILLING_PER_MINUTE,
            'layout_mode' => 'manual_grid',
            'layout_canvas_width' => 1440,
            'layout_canvas_height' => 900,
            'sort_order' => 50,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('services', [
        'code' => 'ps-vip',
        'slug' => 'playstation-vip',
        'name' => 'PlayStation VIP',
        'service_category_id' => $category->id,
        'layout_mode' => 'manual_grid',
        'layout_canvas_width' => 1440,
        'layout_canvas_height' => 900,
    ]);
});

test('service create validates duplicate code and slug', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $category = ServiceCategory::query()->where('code', 'playstation')->firstOrFail();

    $this->actingAs($admin)
        ->from(route('management.services.index'))
        ->post(route('management.services.store'), [
            'service_category_id' => $category->id,
            'code' => 'ps-regular',
            'name' => 'PlayStation Duplicate',
            'slug' => 'ps-4',
            'service_type' => Service::TYPE_TIMED_UNIT,
            'billing_type' => Service::BILLING_PER_MINUTE,
            'sort_order' => 50,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'))
        ->assertSessionHasErrors(['code', 'slug']);
});

test('admins can update services', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->where('code', 'cafe-dine-in')->firstOrFail();
    $category = ServiceCategory::query()->where('code', 'cafe')->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('management.services.update', $service), [
            'service_category_id' => $category->id,
            'code' => 'cafe-dine-in',
            'name' => 'Cafe Indoor',
            'slug' => 'cafe-indoor',
            'service_type' => Service::TYPE_MENU_ONLY,
            'billing_type' => Service::BILLING_FLAT,
            'sort_order' => 45,
            'is_active' => false,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Cafe Indoor',
        'slug' => 'cafe-indoor',
        'sort_order' => 45,
        'is_active' => false,
    ]);
});

test('admins can create service units', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('management.service-units.store'), [
            'service_id' => $service->id,
            'code' => 'ps-05',
            'name' => 'PS-4 Unit 05',
            'zone' => 'PlayStation Zone',
            'status' => ServiceUnit::STATUS_AVAILABLE,
            'capacity' => 4,
            'is_bookable' => true,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_units', [
        'service_id' => $service->id,
        'code' => 'ps-05',
        'name' => 'PS-4 Unit 05',
        'status' => ServiceUnit::STATUS_AVAILABLE,
    ]);
});

test('service unit create validates duplicate code, invalid status, and menu-only service assignment', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->where('code', 'cafe-dine-in')->firstOrFail();

    $this->actingAs($admin)
        ->from(route('management.services.index'))
        ->post(route('management.service-units.store'), [
            'service_id' => $service->id,
            'code' => 'ps-01',
            'name' => 'Invalid Unit',
            'zone' => 'Cafe Zone',
            'status' => 'broken',
            'capacity' => 2,
            'is_bookable' => true,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'))
        ->assertSessionHasErrors(['service_id', 'code', 'status']);
});

test('admins can update service units', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $unit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();
    $service = Service::query()->where('code', 'billiard-regular')->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('management.service-units.update', $unit), [
            'service_id' => $service->id,
            'code' => 'bill-01',
            'name' => 'Billiard Table VIP 01',
            'zone' => 'VIP Billiard Zone',
            'status' => ServiceUnit::STATUS_MAINTENANCE,
            'capacity' => 6,
            'layout_x' => 420,
            'layout_y' => 160,
            'layout_w' => 180,
            'layout_h' => 100,
            'layout_rotation' => 10,
            'layout_z_index' => 3,
            'is_bookable' => false,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_units', [
        'id' => $unit->id,
        'name' => 'Billiard Table VIP 01',
        'zone' => 'VIP Billiard Zone',
        'status' => ServiceUnit::STATUS_MAINTENANCE,
        'capacity' => 6,
        'layout_x' => 420,
        'layout_y' => 160,
        'layout_w' => 180,
        'layout_h' => 100,
        'layout_rotation' => 10,
        'layout_z_index' => 3,
        'is_bookable' => false,
    ]);
});

test('admins can create service pricing rules', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->where('code', 'billiard-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'bill-02')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('management.service-pricing-rules.store'), [
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'pricing_model' => 'per_interval',
            'billing_interval_minutes' => 60,
            'base_price_rupiah' => 10000,
            'price_per_interval_rupiah' => 35000,
            'minimum_charge_rupiah' => 35000,
            'priority' => 30,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_pricing_rules', [
        'service_id' => $service->id,
        'service_unit_id' => $unit->id,
        'priority' => 30,
        'price_per_interval_rupiah' => 35000,
    ]);
});

test('pricing rule create validates unit ownership mismatch', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();
    $unit = ServiceUnit::query()->where('code', 'bill-01')->firstOrFail();

    $this->actingAs($admin)
        ->from(route('management.services.index'))
        ->post(route('management.service-pricing-rules.store'), [
            'service_id' => $service->id,
            'service_unit_id' => $unit->id,
            'pricing_model' => 'per_interval',
            'billing_interval_minutes' => 60,
            'base_price_rupiah' => 0,
            'price_per_interval_rupiah' => 35000,
            'minimum_charge_rupiah' => 35000,
            'priority' => 30,
            'is_active' => true,
        ])
        ->assertRedirect(route('management.services.index'))
        ->assertSessionHasErrors('service_unit_id');
});

test('admins can update service pricing rules', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $pricingRule = ServicePricingRule::query()->where('priority', 10)->whereNull('service_unit_id')->firstOrFail();
    $service = Service::query()->where('id', $pricingRule->service_id)->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('management.service-pricing-rules.update', $pricingRule), [
            'service_id' => $service->id,
            'service_unit_id' => null,
            'pricing_model' => 'per_interval',
            'billing_interval_minutes' => 45,
            'base_price_rupiah' => 5000,
            'price_per_interval_rupiah' => 22000,
            'minimum_charge_rupiah' => 22000,
            'priority' => 15,
            'is_active' => false,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_pricing_rules', [
        'id' => $pricingRule->id,
        'billing_interval_minutes' => 45,
        'base_price_rupiah' => 5000,
        'price_per_interval_rupiah' => 22000,
        'priority' => 15,
        'is_active' => false,
    ]);
});

test('admins can create booking policies', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->create([
        'service_category_id' => ServiceCategory::query()->where('code', 'playstation')->value('id'),
        'code' => 'ps-private',
        'name' => 'PlayStation Private',
        'slug' => 'playstation-private',
        'service_type' => Service::TYPE_TIMED_UNIT,
        'billing_type' => Service::BILLING_PER_MINUTE,
        'is_active' => true,
        'sort_order' => 60,
    ]);

    $this->actingAs($admin)
        ->post(route('management.service-booking-policies.store'), [
            'service_id' => $service->id,
            'slot_interval_minutes' => 30,
            'min_duration_minutes' => 60,
            'max_duration_minutes' => 180,
            'lead_time_minutes' => 15,
            'max_advance_days' => 10,
            'requires_unit_assignment' => true,
            'walk_in_allowed' => true,
            'online_booking_allowed' => false,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_booking_policies', [
        'service_id' => $service->id,
        'max_advance_days' => 10,
        'online_booking_allowed' => false,
    ]);
});

test('booking policy create validates duplicate service policy', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $service = Service::query()->where('code', 'ps-regular')->firstOrFail();

    $this->actingAs($admin)
        ->from(route('management.services.index'))
        ->post(route('management.service-booking-policies.store'), [
            'service_id' => $service->id,
            'slot_interval_minutes' => 30,
            'min_duration_minutes' => 60,
            'max_duration_minutes' => 180,
            'lead_time_minutes' => 15,
            'max_advance_days' => 10,
            'requires_unit_assignment' => true,
            'walk_in_allowed' => true,
            'online_booking_allowed' => true,
        ])
        ->assertRedirect(route('management.services.index'))
        ->assertSessionHasErrors('service_id');
});

test('admins can update booking policies', function () {
    $admin = User::factory()->create([
        'role_id' => Role::query()->where('code', Role::ADMIN)->value('id'),
    ]);

    $bookingPolicy = ServiceBookingPolicy::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('management.service-booking-policies.update', $bookingPolicy), [
            'service_id' => $bookingPolicy->service_id,
            'slot_interval_minutes' => 45,
            'min_duration_minutes' => 90,
            'max_duration_minutes' => 210,
            'lead_time_minutes' => 20,
            'max_advance_days' => 12,
            'requires_unit_assignment' => false,
            'walk_in_allowed' => true,
            'online_booking_allowed' => true,
        ])
        ->assertRedirect(route('management.services.index'));

    $this->assertDatabaseHas('service_booking_policies', [
        'id' => $bookingPolicy->id,
        'slot_interval_minutes' => 45,
        'min_duration_minutes' => 90,
        'max_duration_minutes' => 210,
        'requires_unit_assignment' => false,
    ]);
});

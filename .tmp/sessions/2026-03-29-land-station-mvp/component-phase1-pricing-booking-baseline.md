# Component: Phase 1 Pricing and Booking Policy Baseline

## Goal
Add the first flexible pricing and booking-policy data layer so Land Station services can move away from hardcoded operational rules and toward configurable billing + reservation behavior.

## Scope
- Add baseline tables:
  - `service_pricing_rules`
  - `service_booking_policies`
- Keep money fields compliant with project standards:
  - integer bigint
  - `*_rupiah` suffix
- Add minimal Eloquent models and relations for pricing rules and booking policies.
- Extend `ServiceCatalogSeeder` with baseline pricing rules and booking policies for timed services.
- Preserve existing route and permission behavior.
- Do not implement booking flow, availability resolver, or `service_sessions` yet.

## Files to Create / Modify

### Create
- `database/migrations/*_create_service_pricing_rules_table.php`
- `database/migrations/*_create_service_booking_policies_table.php`
- `app/Models/ServicePricingRule.php`
- `app/Models/ServiceBookingPolicy.php`
- `tests/Feature/Database/ServicePricingBookingBaselineSchemaTest.php`
- `tests/Feature/Database/ServicePricingBookingBaselineSeederTest.php`

### Modify
- `app/Models/Service.php`
- `app/Models/ServiceUnit.php`
- `database/seeders/ServiceCatalogSeeder.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- Adds configurable pricing-rule rows per service or per specific unit.
- Adds one booking-policy row per service.
- No new routes in this component.
- Existing management route remains the future place for viewing/editing these records.

## Validation Plan
- Write failing schema + seeder tests first.
- Verify red with targeted tests.
- Implement minimal migrations/models/seed updates.
- Verify green with targeted tests.
- Rebuild seeded DB to confirm migration/seeder integrity.

## Risks / Open Questions
- Keep this slice minimal: no availability logic yet.
- Do not invent money fields without `*_rupiah` naming.
- `service_unit_id` on pricing rules should be nullable to support service-wide defaults.
- Booking policies should focus on timed/bookable services; cafe can remain without an online-booking rule if not required.

# Component: Phase 1 Service Catalog Baseline

## Goal
Introduce the first flexible business schema slice for Land Station so service definitions and physical units stop being implied by hardcoded UI copy and start living in database-backed master data.

## Scope
- Add baseline tables:
  - `service_categories`
  - `services`
  - `service_units`
- Keep money and timed-session constraints aligned with project standards.
- Add initial Eloquent models and relations for the three tables.
- Seed baseline service categories and a few example services/units for Land Station.
- Preserve current route and permission behavior.
- Do not add pricing, booking policies, or `service_sessions` yet.
- Do not expand permission surface unless strictly required.

## Files to Create / Modify

### Create
- `database/migrations/*_create_service_categories_table.php`
- `database/migrations/*_create_services_table.php`
- `database/migrations/*_create_service_units_table.php`
- `app/Models/ServiceCategory.php`
- `app/Models/Service.php`
- `app/Models/ServiceUnit.php`
- `database/seeders/ServiceCatalogSeeder.php`
- `tests/Feature/Database/ServiceCatalogSchemaTest.php`
- `tests/Feature/Database/ServiceCatalogSeederTest.php`

### Modify
- `database/seeders/DatabaseSeeder.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- Adds flexible master-data tables for category -> service -> unit hierarchy.
- No new routes in this component.
- Current `management.index` remains the internal entrypoint for future CRUD screens.
- Current UI stays mostly static; this component only prepares the data foundation.

## Validation Plan
- Write failing schema tests first.
- Verify red with targeted feature tests.
- Implement minimal migrations.
- Verify green on schema tests.
- Then continue with models + seeder in the next incremental step.

## Risks / Open Questions
- `service_sessions` must not be introduced under the name `sessions` because Laravel already owns that table.
- Money fields must use integer `*_rupiah` naming if any are added later; avoid introducing pricing columns early without the proper naming contract.
- Cafe should be modeled as a service category even though it may later also use separate product catalog tables.
- Unitized vs non-unitized services should be represented by data columns, not by separate service-specific models.

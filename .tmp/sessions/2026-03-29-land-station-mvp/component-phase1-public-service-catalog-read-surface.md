# Component: Phase 1 Public Service Catalog Read Surface

## Goal
Replace the static public services page with a database-backed, guest-accessible service catalog summary so the public-facing website starts reflecting the flexible master-data foundation.

## Scope
- Keep `/services` route and `services.index` name unchanged.
- Load active service categories and active services from DB.
- Surface read-only summary fields only:
  - category description and counts
  - service type / billing type
  - unit count
  - pricing availability
  - booking-policy availability
  - simple starting price summary
- No booking form or CTA flow yet.

## Files to Create / Modify

### Create
- `tests/Feature/Public/PublicServicesIndexTest.php`

### Modify
- `app/Http/Controllers/Public/ServiceController.php`
- `resources/js/Pages/Public/Services/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- `services.index` becomes data-backed for guests.
- Public page uses only shaped summary props; no raw internal records.

## Validation Plan
- Write failing guest feature tests for prop shape/order/pricing summary.
- Verify red.
- Implement minimal controller queries and presentational UI updates.
- Verify green with targeted tests.
- Re-run build + selected phase-one tests.

## Risks / Open Questions
- Keep service ordering deterministic across seeds.
- Avoid exposing unit codes or internal IDs on the public page.
- Keep pricing display simple; do not imply booking capability beyond summary flags in this component.

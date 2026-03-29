# Component: Phase 1 Management Read Surface

## Goal
Replace the static management placeholder page with a data-backed read-only master-data overview so admin users can inspect the seeded service catalog foundation before CRUD screens exist.

## Scope
- Keep `/management` route, name, and permission contract unchanged.
- Load summary data from:
  - `service_categories`
  - `services`
  - `service_units`
  - `service_pricing_rules`
  - `service_booking_policies`
- Render read-only counts and grouped summaries in `Admin/Management/Index`.
- Do not add CRUD actions or new routes.

## Files to Create / Modify

### Create
- `tests/Feature/Admin/ManagementIndexTest.php`

### Modify
- `app/Http/Controllers/Admin/ManagementController.php`
- `resources/js/Pages/Admin/Management/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Existing `management.index` route becomes data-backed.
- Existing permission middleware remains unchanged.

## Validation Plan
- Write failing feature test for `management.index` prop shape and seeded summary counts.
- Verify red.
- Implement minimal controller query + presentational page update.
- Verify green with targeted test.
- Re-run access + public + database phase-one suite.

## Risks / Open Questions
- Keep controller thin; shape only the props needed by the page.
- Avoid exposing raw internal IDs or editable state in this read-only slice.
- Keep page composable and static-friendly; no client-side fetch or state manager needed.

# Component: Phase 7 Customer History Search

## Goal
Add a GET-based search flow to the admin customer history index so operators can quickly find customers by name, phone, or email without changing the current read-only data model.

## Scope
- Extend `reports.customers.index` with a `q` query parameter.
- Search across `customers.name`, `customers.phone`, and `customers.email`.
- Return shaped `filters` props to the page.
- Add a small search UI to the existing customer history index page.
- Keep the customer detail page unchanged.
- No schema changes.

## Files to Create / Modify
- Modify `app/Http/Controllers/Admin/CustomerHistoryController.php`
- Modify `resources/js/Pages/Admin/Customers/Index.jsx`
- Modify `tests/Feature/Admin/CustomerHistoryTest.php`
- Modify `.opencode/CHANGELOG.md`
- Modify `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- No route changes.
- Adds GET filter behavior only.

## Validation Plan
- Write failing feature tests first for name/phone/email search behavior and returned filter props.
- Verify red.
- Implement minimal query filtering and GET form UI.
- Verify green with targeted tests.
- Re-run broader suite.

## Risks / Open Questions
- Preserve deterministic ordering of customer results.
- Keep filter behavior read-only and idempotent.
- Avoid changing current summary calculation semantics.

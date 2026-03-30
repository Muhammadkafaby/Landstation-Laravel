# Component: Phase 7 Customer History CSV Export

## Goal
Add the next export-ready reporting slice so admin users can export the filtered customer history list as CSV for operational follow-up.

## Scope
- Add CSV export action for `reports.customers.index`.
- Keep export admin-only and read-only.
- Reuse existing `q` customer search filter.
- Export safe summary columns only.
- No schema changes.

## Files to Create / Modify
- Modify `app/Http/Controllers/Admin/CustomerHistoryController.php`
- Modify `resources/js/Pages/Admin/Customers/Index.jsx`
- Modify `routes/web.php`
- Modify `tests/Feature/Admin/CustomerHistoryTest.php`
- Modify `.opencode/CHANGELOG.md`
- Modify `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add one route:
  - `reports.customers.export`
- Reuse current `q` semantics from the customer history index.

## Validation Plan
- Write failing feature tests first for:
  - admin CSV export access
  - cashier/non-staff rejection
  - CSV headers and row values
  - filter propagation into exported output
- Verify red.
- Implement minimal export action and button.
- Verify green.
- Re-run focused suite.

## Risks / Open Questions
- Keep export columns aligned with on-screen customer summary fields.
- Avoid exporting notes or raw internal payloads in this slice.
- Preserve existing route protection and index page behavior.

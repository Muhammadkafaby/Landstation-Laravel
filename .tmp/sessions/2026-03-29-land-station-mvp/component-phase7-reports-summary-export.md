# Component: Phase 7 Reports Summary CSV Export

## Goal
Add CSV export for the admin reports summary page so operators can export the currently scoped aggregate metrics for lightweight reporting and accounting follow-up.

## Scope
- Add CSV export action for `reports.index`.
- Keep export admin-only and read-only.
- Reuse the existing `date_scope` filter exactly.
- Export only already-computed summary metrics.
- No schema changes.

## Files to Create / Modify
- Modify `app/Http/Controllers/Admin/ReportController.php`
- Modify `resources/js/Pages/Admin/Reports/Index.jsx`
- Modify `routes/web.php`
- Modify `tests/Feature/Admin/ReportsTest.php`
- Modify `.opencode/CHANGELOG.md`
- Modify `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add one route:
  - `reports.export`
- Reuse current `date_scope` semantics from `reports.index`.

## Validation Plan
- Write failing feature tests first for:
  - admin CSV export access
  - cashier/non-staff rejection
  - CSV headers/rows for summary sections
  - `date_scope` propagation into exported output
- Verify red.
- Implement minimal export action and button.
- Verify green.
- Re-run focused suite.

## Risks / Open Questions
- Keep CSV flat and aggregate-oriented.
- Avoid introducing new reporting semantics in export.
- Keep values raw numeric counts/amounts, not formatted strings.

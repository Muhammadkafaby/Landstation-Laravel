# Component: Phase 6 Reporting Read Surface and Operational Summaries

## Goal
Add the first admin reporting screen so internal users can inspect operational and commercial summaries from bookings, sessions, orders, invoices, and payments without mutating business state.

## Scope
- Add admin-only reports page.
- Keep `/dashboard` intact and additive.
- Expose shaped read-only summary props only.
- No schema changes.
- No report export/download yet.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Admin/ReportController.php`
- `resources/js/Pages/Admin/Reports/Index.jsx`
- `tests/Feature/Admin/ReportsTest.php`

### Modify
- `routes/web.php`
- `resources/js/Pages/Admin/Dashboard/Index.jsx`
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add `reports.index` under existing admin access middleware.
- Reuse existing bookings, service sessions, orders, invoices, and payments tables.

## Validation Plan
- Write failing feature tests first for:
  - admin access
  - cashier rejection
  - expected summary aggregates from seeded/manual fixtures
- Verify red.
- Implement thin controller + presentational page.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Keep reporting deterministic and aggregate-only.
- Do not dump raw payloads or snapshots into UI.
- Use verified payments for collected revenue, not pending/failed rows.

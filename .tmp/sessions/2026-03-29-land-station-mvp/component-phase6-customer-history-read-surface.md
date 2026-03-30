# Component: Phase 6 Customer History and Transaction History Read Surface

## Goal
Add the first customer-centric internal read surface so admin users can inspect customer activity and transaction history across bookings, sessions, orders, invoices, and payments.

## Scope
- Add admin-only customer history list page.
- Add admin-only customer detail page.
- Keep the slice read-only and additive.
- No schema changes.
- No export/filter/pagination yet.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Admin/CustomerHistoryController.php`
- `resources/js/Pages/Admin/Customers/Index.jsx`
- `resources/js/Pages/Admin/Customers/Show.jsx`
- `tests/Feature/Admin/CustomerHistoryTest.php`

### Modify
- `routes/web.php`
- `resources/js/Pages/Admin/Reports/Index.jsx`
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add routes:
  - `reports.customers.index`
  - `reports.customers.show`
- Reuse `customers`, `bookings`, `service_sessions`, `orders`, `invoices`, and `payments`.

## Validation Plan
- Write failing feature tests for:
  - admin access
  - cashier/non-staff rejection
  - customer summary aggregates on list page
  - customer detail timeline props on show page
- Verify red.
- Implement thin controller + presentational pages.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Keep payloads shaped and aggregate-focused; do not expose raw internal snapshots.
- Use verified payments only for collected revenue totals.
- Keep ordering deterministic for timeline assertions.

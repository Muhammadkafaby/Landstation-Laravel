# Component: Phase 7 Transaction Ledger CSV Export

## Goal
Add the first export-ready reporting flow so admin users can export the filtered transaction ledger as CSV for operational and accounting follow-up.

## Scope
- Add CSV export action for `reports.transactions.index`.
- Keep the export admin-only and read-only.
- Reuse existing ledger filters:
  - `q`
  - `status`
  - `payment_method`
- Export only safe, shaped commercial columns.
- No schema changes.

## Files to Create / Modify
- Modify `app/Http/Controllers/Admin/TransactionLedgerController.php`
- Modify `resources/js/Pages/Admin/Reports/Transactions/Index.jsx`
- Modify `routes/web.php`
- Modify `tests/Feature/Admin/TransactionLedgerTest.php`
- Modify `.opencode/CHANGELOG.md`
- Modify `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add one new route:
  - `reports.transactions.export`
- Reuse current ledger query and filter semantics.

## Validation Plan
- Write failing feature tests first for:
  - admin CSV export access
  - cashier/non-staff rejection
  - CSV header and row output
  - GET filter propagation into export output
- Verify red.
- Implement minimal export action and button.
- Verify green.
- Re-run focused suite.

## Risks / Open Questions
- Preserve current admin-only route protection.
- Keep exported values aligned with on-screen ledger math.
- Avoid exporting raw JSON payloads or internal snapshots.

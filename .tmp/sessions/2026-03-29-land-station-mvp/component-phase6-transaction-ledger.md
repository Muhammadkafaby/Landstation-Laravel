# Component: Phase 6 Transaction Ledger and Reporting Drill-Down

## Goal
Add the first invoice-centric transaction ledger so admin users can inspect commercial history with line-level and payment-level drill-down in one read-only reporting surface.

## Scope
- Add admin-only transaction ledger page.
- Keep the slice read-only.
- No schema changes.
- No export/filter/pagination yet.
- No separate detail route yet; drill-down stays inline.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Admin/TransactionLedgerController.php`
- `resources/js/Pages/Admin/Reports/Transactions/Index.jsx`
- `tests/Feature/Admin/TransactionLedgerTest.php`

### Modify
- `routes/web.php`
- `resources/js/Pages/Admin/Reports/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add route `reports.transactions.index` under admin access middleware.
- Reuse invoices, invoice lines, payments, customers, bookings, and service sessions.

## Validation Plan
- Write failing feature tests for:
  - admin access
  - cashier/non-staff rejection
  - deterministic invoice ledger props
  - verified/remaining balance calculations
- Verify red.
- Implement thin controller + presentational page.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Keep ledger read-only and shape only safe fields.
- Use verified payments only for collected amounts.
- Keep ordering deterministic by `issued_at desc`, then `invoice_code desc`.

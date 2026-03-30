# Component: Phase 8 Transaction Ledger Pagination

## Goal
Add pagination to the admin transaction ledger so larger invoice histories remain performant and usable while preserving existing filters and export behavior.

## Scope
- Paginate `reports.transactions.index`.
- Preserve existing GET filters across pages:
  - `q`
  - `status`
  - `payment_method`
- Keep CSV export full-result and unchanged.
- Keep ordering, filters, and ledger item shape consistent.
- No schema changes.

## Files to Create / Modify
- Modify `app/Http/Controllers/Admin/TransactionLedgerController.php`
- Modify `resources/js/Pages/Admin/Reports/Transactions/Index.jsx`
- Modify `tests/Feature/Admin/TransactionLedgerTest.php`
- Modify `.opencode/CHANGELOG.md`
- Modify `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- No route changes.
- Pagination only affects the on-screen ledger page.

## Validation Plan
- Write failing feature tests first for paginator shape and query preservation.
- Verify red.
- Implement minimal paginated controller response and UI links.
- Verify green.
- Re-run focused suite.

## Risks / Open Questions
- Preserve current export output and filter semantics exactly.
- Preserve deterministic ordering by `issued_at desc`, then `invoice_code desc`.
- Keep verified/remaining ledger calculations unchanged.

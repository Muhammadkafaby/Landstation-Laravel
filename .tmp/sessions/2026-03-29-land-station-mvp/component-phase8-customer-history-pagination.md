# Component: Phase 8 Customer History Pagination

## Goal
Add pagination to the admin customer history list so larger datasets remain performant and usable without changing the current read-only customer summary semantics.

## Scope
- Paginate `reports.customers.index`.
- Preserve existing `q` search filter across pages.
- Keep customer detail page unchanged.
- Keep export full-result and unchanged.
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
- Pagination only affects on-screen customer list props.

## Validation Plan
- Write failing feature tests first for paginator shape and query preservation.
- Verify red.
- Implement minimal paginator response and UI links.
- Verify green.
- Re-run focused suite.

## Risks / Open Questions
- Preserve current aggregate calculations per customer.
- Preserve `q` during page navigation.
- Keep export route full dataset, not paginated.

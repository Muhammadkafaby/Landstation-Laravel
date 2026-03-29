# Component: Phase 2 Dashboard Read Surface

## Goal
Replace the admin dashboard placeholder cards with a DB-backed operational summary so admin users can inspect the current service foundation from one internal overview.

## Scope
- Keep `/dashboard` route and `dashboard` name unchanged.
- Keep existing permission/auth contract unchanged.
- Load read-only summary data from seeded service catalog tables.
- Expose only guest-safe/internal summary props, not raw records.
- No CRUD, booking flow, session flow, transaction flow, or reporting queries yet.

## Files to Create / Modify

### Create
- `tests/Feature/Admin/DashboardIndexTest.php`

### Modify
- `app/Http/Controllers/Admin/DashboardController.php`
- `resources/js/Pages/Admin/Dashboard/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Existing `dashboard` route becomes data-backed.
- Existing login/email verification redirect expectations remain valid because route name stays the same.

## Validation Plan
- Write failing feature tests for seeded summary props and category operational cards.
- Verify red.
- Implement thin controller queries + presentational dashboard update.
- Verify green.
- Re-run build + selected auth/access/public/database suite.

## Risks / Open Questions
- Keep dashboard lighter than future reports page.
- Do not expose raw pricing rows or unit identifiers.
- Preserve deterministic ordering and existing route name for auth flows.

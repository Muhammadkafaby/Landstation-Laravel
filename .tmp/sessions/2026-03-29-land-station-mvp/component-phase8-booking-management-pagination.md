# Component: Phase 8 Booking Management Pagination

## Goal
Add pagination to the internal booking management list so larger operational booking queues remain usable without changing the existing lifecycle transition logic.

## Scope
- Paginate `management.bookings.index`.
- Keep current status transition actions unchanged.
- Preserve current ordering.
- No schema changes.
- No new filters/search in this slice.

## Files to Modify
- `app/Http/Controllers/Admin/BookingManagementController.php`
- `resources/js/Pages/Admin/Bookings/Index.jsx`
- `tests/Feature/Admin/BookingManagementTest.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Validation Plan
- Write failing tests first for paginator shape.
- Verify red.
- Implement minimal paginated response and UI links.
- Verify green.
- Re-run focused suite.

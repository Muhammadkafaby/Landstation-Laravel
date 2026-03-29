# Component: Phase 3 Booking Management Listing and Lifecycle Transitions

## Goal
Add the first internal booking management surface so staff with `manage-bookings` can view bookings and perform valid lifecycle transitions without breaking existing create flows.

## Scope
- Add internal booking listing page.
- Add one status-transition endpoint.
- Keep public and internal create flows unchanged.
- Reuse `manage-bookings` permission.
- Enforce minimal lifecycle matrix only.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Admin/BookingManagementController.php`
- `app/Http/Requests/Admin/TransitionBookingStatusRequest.php`
- `app/Services/Booking/BookingStatusManager.php`
- `resources/js/Pages/Admin/Bookings/Index.jsx`

### Modify
- `routes/web.php`
- `resources/js/Pages/Admin/Bookings/Create.jsx`
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `tests/Feature/Admin/BookingManagementTest.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add `management.bookings.index` and transition route.
- No changes to booking creation routes.

## Validation Plan
- Write failing feature tests for list access, valid transitions, and invalid lifecycle jumps.
- Verify red.
- Implement thin controller + request + booking status manager.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Keep terminal states immutable.
- Do not infer payment/session completion rules yet.
- Keep transition matrix small and explicit.

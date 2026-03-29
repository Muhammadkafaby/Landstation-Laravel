# Component: Phase 3 Booking Create Flow Foundation

## Goal
Add the first public and internal booking entry flow on top of the booking schema and availability resolver so Land Station can create valid timed-service bookings through the web app.

## Scope
- Add guest-accessible booking create page + submit flow.
- Add internal booking create page + submit flow guarded by `manage-bookings`.
- Reuse `TimedServiceAvailabilityResolver` for policy validation and unit availability checks.
- Create customer + booking records.
- Keep this slice create-only; no booking list/update/status transition yet.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Public/BookingController.php`
- `app/Http/Controllers/Admin/BookingController.php`
- `app/Http/Requests/Public/StoreBookingRequest.php`
- `app/Http/Requests/Admin/StoreBookingRequest.php`
- `app/Services/Booking/BookingCreator.php`
- `resources/js/Components/Bookings/BookingForm.jsx`
- `resources/js/Pages/Public/Bookings/Create.jsx`
- `resources/js/Pages/Admin/Bookings/Create.jsx`
- `tests/Feature/Public/BookingFlowTest.php`
- `tests/Feature/Admin/BookingManagementTest.php`

### Modify
- `routes/web.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add public routes for booking create/store.
- Add internal booking routes under `manage-bookings` permission.
- Reuse existing `customers`, `bookings`, `service_booking_policies`, and `service_units`.

## Validation Plan
- Write failing feature tests for page rendering, valid create, policy violation, and overlap rejection.
- Verify red.
- Implement minimal controllers/requests/service/pages.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Public create flow will be server-validated; client-side live availability can come later.
- Booking pricing snapshot will stay minimal until a dedicated pricing resolver is introduced.
- Cashiers also have `manage-bookings`; internal routes should allow them by permission even if the page lives under `Admin/Bookings`.

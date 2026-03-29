# Component: Phase 3 Booking Schema and Relation Foundation

## Goal
Introduce the first booking-domain schema so Land Station can move from master-data-only configuration into customer, reservation, and timed-session foundations without breaking current admin/public modules.

## Scope
- Add baseline tables:
  - `customers`
  - `bookings`
  - `service_sessions`
- Add first models and relations for the three tables.
- Keep routes/UI unchanged for this component.
- Keep this slice schema-first; do not implement booking UI or availability resolver yet.
- Respect project constraints:
  - use `service_sessions`, never `sessions`
  - money snapshots stay integer rupiah-compatible
  - status values live in constants

## Files to Create / Modify

### Create
- `database/migrations/*_create_customers_table.php`
- `database/migrations/*_create_bookings_table.php`
- `database/migrations/*_create_service_sessions_table.php`
- `app/Models/Customer.php`
- `app/Models/Booking.php`
- `app/Models/ServiceSession.php`
- `tests/Feature/Database/BookingFoundationSchemaTest.php`
- `tests/Feature/Database/BookingFoundationRelationsTest.php`

### Modify
- `app/Models/Service.php`
- `app/Models/ServiceUnit.php`
- `app/Models/User.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- Adds booking/customer/session domain tables only.
- No route changes.
- Existing admin/public read surfaces should remain untouched.
- New schema is overlap-ready through `start_at`, `end_at`, `status`, and unit references.

## Validation Plan
- Write failing schema and relation tests first.
- Verify red.
- Implement minimal migrations/models/relations.
- Verify green with targeted tests.
- Re-run access regression and selected suite.

## Risks / Open Questions
- `service_sessions` must stay separate from Laravel auth sessions.
- Current component does not yet add `booking_status_logs` or availability service; that stays for later.
- Timed booking/session status flow should be minimal but future-safe.

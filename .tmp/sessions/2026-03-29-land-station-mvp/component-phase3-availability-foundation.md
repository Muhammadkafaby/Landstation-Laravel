# Component: Phase 3 Availability Resolver and Booking Validation Foundation

## Goal
Add the first reusable availability service for timed services so the booking domain can validate policy windows and calculate which units are free without introducing UI flows yet.

## Scope
- Add one service class under `app/Services/Availability`.
- Target timed services only.
- Validate booking policy constraints:
  - slot interval
  - min duration
  - max duration
  - lead time
  - max advance days
- Resolve available units by excluding:
  - inactive / non-bookable / blocked-status units
  - overlapping active bookings
  - overlapping active or paused service sessions
- Keep routes/UI/auth unchanged.

## Files to Create / Modify

### Create
- `app/Services/Availability/TimedServiceAvailabilityResolver.php`
- `tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php`

### Modify
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- No route changes.
- Reuses existing `services`, `service_units`, `service_booking_policies`, `bookings`, and `service_sessions`.

## Validation Plan
- Write failing tests first for policy validation and overlap filtering.
- Verify red.
- Implement minimal resolver.
- Verify green with focused unit tests.
- Re-run broader access/database regression suite.

## Risks / Open Questions
- Keep this resolver backend-only; booking controllers/forms come later.
- Use existing status constants; avoid new magic strings.
- Future operating-hours logic is out of scope for this component.

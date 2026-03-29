# Component: Phase 4 POS Service Session Start/Stop Foundation

## Goal
Add the first POS-oriented service session lifecycle so staff can start and stop timed service sessions safely, with overlap protection and pricing snapshots persisted at session start.

## Scope
- Add POS session index page.
- Add session start endpoint.
- Add session stop endpoint.
- Support walk-in or booking-linked session start.
- Persist start-time pricing snapshot and billed minutes.
- Keep this slice minimal:
  - no pause/resume yet
  - no invoice/checkout integration yet
  - no unit-status mutation yet

## Files to Create / Modify

### Create
- `app/Http/Controllers/Pos/SessionController.php`
- `app/Http/Requests/Pos/StartServiceSessionRequest.php`
- `app/Http/Requests/Pos/StopServiceSessionRequest.php`
- `app/Services/Sessions/ServiceSessionService.php`
- `resources/js/Pages/Pos/Sessions/Index.jsx`
- `tests/Feature/Pos/ServiceSessionLifecycleTest.php`

### Modify
- `routes/web.php`
- `resources/js/Pages/Pos/Dashboard/Index.jsx`
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add routes:
  - `pos.sessions.index`
  - `pos.sessions.store`
  - `pos.sessions.stop`
- Reuse existing `service_sessions`, `bookings`, `services`, `service_units`, and pricing rules.

## Validation Plan
- Write failing feature tests first for:
  - cashier access to POS session page
  - valid session start
  - start rejection on occupied/blocked unit
  - valid session stop with billed minutes
  - invalid stop on non-active session
- Verify red.
- Implement thin controllers + FormRequests + service class.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Prevent double-start on same unit with transaction-safe checks.
- Linked booking must match service/unit and be in `confirmed` or `checked_in` state.
- Pricing snapshot should be resolved once at start and never recalculated inside this slice.
- Billed minutes should be captured on stop; monetary settlement comes later.

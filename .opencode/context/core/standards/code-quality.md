# Code Quality Standards

## Stack
- Backend: Laravel 12
- Frontend: Inertia.js + React
- Styling: Tailwind CSS
- Database: MySQL 8

## Domain Rules
- Money values use integer rupiah fields (`*_rupiah`) with bigint storage.
- Timed billing uses `per minute` charging.
- Pricing model is `start-time pricing`: rate is resolved once at session start and stored as a snapshot.
- Business session table name is `service_sessions`, never `sessions`.
- QRIS for v1 is `manual static QR`, verified by cashier.

## Architecture
- Keep controllers thin; move business logic to dedicated services/actions.
- Prefer explicit modules: `Public`, `Admin`, `Pos`, `Shared`.
- Use Form Requests for validation.
- Use Policies / Gates / middleware for authorization.
- Store status values consistently through constants or backed enums at application layer.
- Persist critical business snapshots for pricing, payment, and promotions.

## Coding Rules
- Prefer small focused classes and components.
- Avoid hardcoded magic strings for statuses and permission codes.
- Use descriptive names over comments.
- Add comments only for non-obvious business constraints.
- Keep React pages/components composable and presentational where possible.
- Reuse shared UI primitives before adding new variants.

## Reliability Rules
- Prevent overlapping active sessions on the same unit.
- Prevent overlapping active bookings on the same unit and slot.
- Never mutate paid transactions except through explicit future reversal flows.
- Log audit events for pricing overrides, payment verification, unit moves, and booking status changes.

## Validation Rules
- Validate all monetary inputs and convert to integer rupiah at boundaries.
- Validate datetime and timezone assumptions on every booking/session input.
- Reject state transitions that skip required lifecycle steps.

## Project Structure
- `app/Http/Controllers/Public`
- `app/Http/Controllers/Admin`
- `app/Http/Controllers/Pos`
- `app/Services`
- `app/Actions`
- `resources/js/Pages/Public`
- `resources/js/Pages/Admin`
- `resources/js/Pages/Pos`
- `resources/js/Components`

# Public Booking Hold And Manual Layout Design

**Date:** 2026-03-31  
**Status:** Draft for review  
**Scope:** Public booking, staff confirmation, shared availability, manual unit layout management

## Goal

Add a public booking flow that lets guests reserve a specific unit without logging in, using a visual layout similar to cinema seat booking, while keeping staff and public views fully integrated.

The system must:

- allow guests to book directly from the public website without login
- lock the selected unit immediately after submit
- give staff 10 minutes to confirm the booking manually
- release the slot automatically if staff do not confirm within 10 minutes
- use one shared source of truth for public availability and staff booking management
- let admins manage unit positions through a manual layout editor in the management panel
- prevent concurrent double-booking during hold creation and confirmation
- include basic abuse protection for anonymous public holds

## Confirmed Product Decisions

- Public users book without login.
- Booking creates an immediate temporary hold.
- Staff confirm manually.
- Hold duration is 10 minutes.
- The interaction model is like cinema seat booking.
- Public booking uses a visual layout, not a plain dropdown.
- Different service types use the same layout engine:
  - rooms
  - billiard tables
  - RC units
- Layout is managed by admin from the management panel, not hardcoded in code.

## Current Repo Baseline

The repository already contains a useful booking foundation:

- public booking routes in `routes/web.php`
- public booking controller in `app/Http/Controllers/Public/BookingController.php`
- booking creation service in `app/Services/Booking/BookingCreator.php`
- booking transition service in `app/Services/Booking/BookingStatusManager.php`
- timed availability logic in `app/Services/Availability/TimedServiceAvailabilityResolver.php`
- public booking UI in:
  - `resources/js/Pages/Public/Bookings/Create.jsx`
  - `resources/js/Components/Bookings/BookingForm.jsx`
- admin service management UI in `resources/js/Pages/Admin/Services/Index.jsx`
- public booking tests in `tests/Feature/Public/BookingFlowTest.php`

Main gap: the current flow creates `pending` bookings from a form and blocks availability, but it has no hold expiration concept, no visual unit map, and no admin-managed manual layout.

## Recommended Approach

Extend the existing booking model and availability flow instead of introducing a separate hold domain.

### Why this approach

- It fits the current architecture.
- The existing availability logic already blocks based on booking status.
- Public and staff can read from the same booking records.
- It minimizes duplicate domain concepts.

### Alternatives considered

#### 1. Separate `booking_holds` table

Pros:

- clean distinction between temporary holds and confirmed bookings

Cons:

- adds more query complexity
- duplicates booking-like data
- increases public/staff synchronization complexity

#### 2. Hardcoded visual layout with backend-only hold logic

Pros:

- faster short-term UI delivery

Cons:

- conflicts with the requirement that admin manages layout from the panel
- does not scale cleanly across rooms, billiard, and RC

## Architecture

Use a single booking and availability source shared by public and staff.

- `service_units` remain the physical assets that can be booked.
- layout metadata is stored in data, not hardcoded in frontend components
- public booking creates a temporary blocking record
- staff confirm or cancel the same record
- availability is resolved from booking state and time, not from separate UI state

### Shared flow

1. Public user selects service, time, duration, and a unit from the visual map.
2. Backend creates a temporary held booking and sets an expiry time 10 minutes ahead.
3. The held booking blocks availability immediately.
4. Staff see the held booking in management and can confirm or cancel it.
5. If staff confirm before expiry, the booking becomes confirmed.
6. If staff do nothing until expiry, the booking becomes expired and stops blocking the slot.

### Concurrency rule

Hold creation and staff confirmation must both re-check availability inside a single transaction.

The implementation must not rely on:

- a read-first availability check outside the write transaction
- frontend state
- polling freshness

Instead, the create and confirm paths must use database-backed concurrency protection so two near-simultaneous requests cannot both win the same unit and time window.

The implementation plan should include one concrete enforcement strategy, such as:

- row locking on the target unit plus transactional overlap re-check
- or another database-safe mechanism that guarantees only one active overlapping hold or booking survives

## Domain Model Changes

## Booking statuses

Keep the existing lifecycle, but add temporary hold semantics explicitly.

Recommended statuses:

- `held`
- `confirmed`
- `checked_in`
- `completed`
- `cancelled`
- `no_show`
- `expired`

### Status meaning

- `held`: created by public booking and blocks the slot only until expiry
- `confirmed`: manually confirmed by staff and continues to block the slot
- `checked_in`: guest has arrived and operational session can continue
- `completed`: booking/session completed
- `cancelled`: manually cancelled
- `no_show`: confirmed booking not used
- `expired`: temporary hold timed out without staff confirmation

### Transition rules

- `held -> confirmed`
- `held -> cancelled`
- `held -> expired`
- `confirmed -> checked_in`
- `confirmed -> cancelled`
- `confirmed -> no_show`
- `checked_in -> completed`

No transition should be allowed out of `expired`, `completed`, `cancelled`, or `no_show`.

## Booking table changes

Add the following fields to `bookings`:

- `hold_expires_at` timestamp nullable
- `confirmed_at` timestamp nullable
- `expired_at` timestamp nullable
- `status_reason` string nullable or `status_notes` text nullable

### Field rules

- `hold_expires_at` is required for `held`
- `confirmed_at` is set only when staff confirm
- `expired_at` is set when a hold times out
- `created_by_user_id` remains nullable for public-origin bookings

## Layout data changes

The layout must be editable by admin and readable by both public and staff UI.

### Unit-level layout metadata

Add fields to `service_units` or a dedicated layout table for:

- `layout_x`
- `layout_y`
- `layout_w`
- `layout_h`
- `layout_rotation`
- `layout_z_index`
- `layout_meta_json`

This metadata controls each unit's position and presentation in the map.

### Service-level layout metadata

Add service-level layout configuration so each service has its own canvas:

- `layout_mode`
- `layout_canvas_width`
- `layout_canvas_height`
- `layout_background_image_path` nullable
- `layout_meta_json`

This makes the same renderer work for:

- room layouts
- billiard tables
- RC layouts

### Recommended storage shape

Preferred implementation:

- service-level layout config on `services`
- unit-level positioning on `service_units`

This keeps layout ownership aligned with existing master data.

## Availability Rules

Update `TimedServiceAvailabilityResolver` so blocking behavior depends on both status and expiration.

### Blocking bookings

Bookings block a unit only when:

- status is `held` and `hold_expires_at > now()`
- status is `confirmed`
- status is `checked_in`

Bookings do not block when:

- status is `expired`
- status is `cancelled`
- status is `completed`
- status is `no_show`

### Time overlap rules

The current overlap behavior should stay:

- booking start must be before candidate end
- booking end must be after candidate start

The only change is whether a booking still counts as active for blocking.

### Scheduler safety rule

Availability must treat expired holds as non-blocking even if the scheduled cleanup job has not run yet.

That means the source of truth is:

- status plus current time
- not status alone

### Global expiry rule

Every read and write path must treat `held` bookings with `hold_expires_at <= now()` as expired immediately, even before the cleanup job updates the persisted status.

This applies to:

- availability queries
- staff hold queue queries
- staff confirm actions
- public status views

That means:

- staff queues must exclude expired holds or relabel them as expired
- confirm actions must fail server-side once the deadline passes
- public pages must never continue showing an expired hold as actionable

## Public UX Design

Replace the current plain booking form flow with a visual map-based selection flow.

### Public flow

1. User opens the public booking page for a service.
2. User chooses date, start time, and duration.
3. Backend returns the current visual map state for that time window.
4. The page renders a service-specific visual map.
5. User selects a specific unit directly from the map.
6. User fills customer identity fields.
7. User submits booking.
8. Backend creates a held booking and returns:
   - booking code
   - hold expiration time
   - current hold status
9. UI shows a countdown and a waiting-for-staff-confirmation state.
10. If confirmed, UI updates to confirmed state.
11. If expired, UI shows the hold expired and asks the user to choose again.

### Public map states

Each unit in the map should clearly show one of:

- available
- held
- confirmed or unavailable
- maintenance
- inactive

### Public polling behavior

The public page should refresh the visible state on a short interval so that:

- held units appear quickly to other users
- expiry is visible without manual refresh
- the current user sees confirm or expire state changes

Polling is acceptable for the first implementation. Real-time push can stay out of scope.

## Staff UX Design

Staff need a management view that surfaces temporary holds clearly and lets them confirm quickly.

### Staff queue behavior

Add a queue to booking management for records in `held` state.

Each row should show:

- booking code
- customer name
- customer phone
- service
- selected unit
- booking window
- countdown or minutes remaining
- source as `public`

### Staff actions

Staff can:

- confirm
- cancel

The queue should update frequently enough that staff can act before holds expire.

If a hold expires before a staff member clicks confirm, the server must reject the confirm action and the UI must refresh that record to expired state.

### Shared truth guarantee

Public and staff views must both read the same booking and availability data so that:

- a staff confirmation is immediately reflected on the public side
- an expiry is immediately reflected on the staff side
- there is no separate local-only slot state

## Admin Layout Editor Design

Add layout management to the existing service management area.

### Admin responsibilities

Admin can:

- choose which service layout is being edited
- set canvas size and optional background
- position units on the canvas
- resize units
- rotate units if needed
- save the layout

### Editor behavior

The editor should be part of the management UI so layout remains master data, not a developer-owned asset.

Recommended initial capabilities:

- drag and drop positioning
- resize handles
- simple rotation input
- reset or snap-to-grid support

Out of scope for the first version:

- collaborative editing
- advanced floorplan tooling
- version history for layouts

## Backend Implementation Design

## Booking creation

Update `BookingCreator` so public booking creation:

- stores `status = held`
- sets `hold_expires_at = now() + 10 minutes`
- preserves current customer resolution behavior
- preserves pricing snapshot behavior

## Booking transition service

Update `BookingStatusManager` so it:

- supports `held -> confirmed`
- supports `held -> cancelled`
- supports `held -> expired`
- sets related timestamps like `confirmed_at` and `expired_at`
- keeps audit logging for important transitions

## Expiry processing

Use two layers:

### 1. Runtime truth

Availability logic must ignore held bookings past their expiration even before cleanup.

### 2. Scheduled cleanup

Add a scheduled command or job that periodically marks expired holds as `expired`.

This keeps reporting, management filters, and operational visibility clean.

Cleanup is not allowed to be the primary correctness mechanism. Its job is persistence hygiene, not slot protection.

## API and page contracts

The public booking page will need backend props or endpoints that return:

- selected service information
- layout config for the service
- positioned units
- unit states for a requested time window
- hold information after booking submission

The admin management page will need:

- editable layout config
- unit layout metadata
- booking hold queue data for staff

## Testing Strategy

Use TDD when implementing the feature. The highest-value tests are:

### Feature tests

- guest can create a held booking
- held booking blocks the chosen unit immediately
- second guest cannot take the same unit while hold is active
- concurrent create requests for the same unit and time window cannot both succeed
- held booking stops blocking after expiry
- staff can confirm held booking before expiry
- expired booking cannot be confirmed
- staff can cancel held booking
- staff queue does not surface expired holds as actionable

### Unit tests

- availability resolver blocks active held bookings
- availability resolver ignores expired held bookings
- booking status manager allows valid transitions only
- booking creator sets `hold_expires_at` correctly

### UI contract tests

- public page receives layout metadata and unit status data
- management page receives editable layout data
- held queue shows remaining time

### Regression coverage

Expand the existing tests:

- `tests/Feature/Public/BookingFlowTest.php`
- `tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php`
- `tests/Feature/Admin/BookingManagementTest.php`

## Rollout Plan

Implement in this order:

### Slice 1. Hold lifecycle

- schema changes
- held and expired statuses
- hold expiration logic
- booking transition updates
- tests

### Slice 2. Public visual booking map

- service layout props
- unit status mapping
- public visual selection UI
- countdown state

### Slice 3. Staff confirmation queue

- management queue for held bookings
- confirm and cancel actions
- countdown visibility

### Slice 4. Admin layout editor

- canvas config
- drag and drop unit placement
- save layout metadata

This order keeps each slice useful while building toward the full experience.

## Risks And Mitigations

### Risk: stale slot state

Mitigation:

- always resolve availability from backend time and status
- use polling on public and staff pages

### Risk: expired hold still looks blocked

Mitigation:

- availability checks must use `hold_expires_at > now()`
- cleanup job is secondary, not primary

### Risk: layout model becomes too rigid

Mitigation:

- keep service-level canvas config separate from unit-level placement
- store extensible presentation data in JSON where needed

### Risk: staff queue becomes operational bottleneck

Mitigation:

- surface time remaining clearly
- keep confirm and cancel as fast inline actions

### Risk: anonymous abuse floods temporary holds

Mitigation:

- apply route rate limiting on public booking create and availability requests
- enforce a per-phone, per-email, or per-IP cap on simultaneous active holds
- add lightweight bot friction appropriate for no-login flows
- make abusive holds visible to staff through source metadata and timestamps

## Out Of Scope

These are not required for the first version:

- public customer accounts
- payment gateway confirmation
- customer self-confirmation by email or WhatsApp link
- realtime websocket updates
- advanced layout version history
- multi-branch or branch-specific layout management

## Abuse Protection Requirements

Because the flow allows no-login public holds that block inventory for 10 minutes, the first implementation must include minimum abuse controls.

Required controls:

- request rate limiting on public availability and booking submit endpoints
- limit on how many active held bookings one actor can own concurrently
- actor identity resolved from some combination of phone, email, and IP address
- server-side enforcement, not frontend-only messaging

Optional but recommended:

- CAPTCHA or another lightweight bot challenge if abuse appears in production
- staff-facing visibility for repeated failed or suspicious hold attempts

## Implementation Handoff

This spec assumes the next step will be a written implementation plan that translates the design into concrete code slices, tests, and file ownership.

Recommended first implementation target:

1. add hold fields and statuses
2. update availability logic
3. update public booking creation to create `held`
4. add expiry cleanup
5. add tests for hold behavior

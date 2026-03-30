# Public Booking Hold Lifecycle Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the first production slice of public booking holds so public bookings create 10-minute holds, expired holds stop blocking availability, and staff transitions respect the new lifecycle.

**Architecture:** Extend the existing booking foundation instead of creating a parallel hold domain. The first slice stays backend-focused: schema, model constants, lifecycle services, availability rules, cleanup command, and tests. Public visual layout and staff queue UI remain for later slices.

**Tech Stack:** Laravel 12, PHP 8.2, Pest, Inertia.js, MySQL

---

## File Map

- Create: `database/migrations/2026_03_31_000001_add_hold_lifecycle_columns_to_bookings_table.php`
- Create: `app/Console/Commands/ExpireHeldBookingsCommand.php`
- Modify: `app/Models/Booking.php`
- Modify: `app/Services/Booking/BookingCreator.php`
- Modify: `app/Services/Booking/BookingStatusManager.php`
- Modify: `app/Services/Availability/TimedServiceAvailabilityResolver.php`
- Modify: `app/Http/Controllers/Admin/BookingManagementController.php`
- Modify: `app/Http\Requests/Admin/TransitionBookingStatusRequest.php`
- Modify: `app/Http/Requests/Booking/BaseStoreBookingRequest.php`
- Modify: `routes/console.php`
- Test: `tests/Feature/Public/BookingFlowTest.php`
- Test: `tests/Feature/Admin/BookingManagementTest.php`
- Test: `tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php`
- Create or expand: `tests/Feature/Console/ExpireHeldBookingsCommandTest.php`

## Chunk 1: Hold Schema And Model

### Task 1: Add failing schema and public booking tests for held lifecycle

**Files:**
- Modify: `tests/Feature/Public/BookingFlowTest.php`
- Modify: `tests/Feature/Admin/BookingManagementTest.php`
- Modify: `tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php`

- [ ] **Step 1: Write failing tests for held booking creation**

Add tests asserting:
- public booking persists `status = held`
- `hold_expires_at` is set to 10 minutes after current time
- expired held bookings do not block availability
- expired held bookings cannot be confirmed

- [ ] **Step 2: Run targeted tests to verify they fail**

Run:
```bash
php artisan test tests/Feature/Public/BookingFlowTest.php tests/Feature/Admin/BookingManagementTest.php tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php
```

Expected:
- FAIL because `held` and `hold_expires_at` do not exist yet

### Task 2: Implement schema and model updates

**Files:**
- Create: `database/migrations/2026_03_31_000001_add_hold_lifecycle_columns_to_bookings_table.php`
- Modify: `app/Models/Booking.php`

- [ ] **Step 1: Add the migration**

Add nullable timestamps:
- `hold_expires_at`
- `confirmed_at`
- `expired_at`

Add nullable status detail field:
- `status_reason` or `status_notes`

- [ ] **Step 2: Update `Booking` model constants and casts**

Add status constants:
- `held`
- `expired`

Add datetime casts for:
- `hold_expires_at`
- `confirmed_at`
- `expired_at`

- [ ] **Step 3: Run the failing tests again**

Run:
```bash
php artisan test tests/Feature/Public/BookingFlowTest.php tests/Feature/Admin/BookingManagementTest.php tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php
```

Expected:
- FAIL moves from missing schema/constants to behavior gaps

## Chunk 2: Hold Creation And Expiry-Aware Availability

### Task 3: Make public bookings create held records

**Files:**
- Modify: `app/Services/Booking/BookingCreator.php`
- Modify: `tests/Feature/Public/BookingFlowTest.php`

- [ ] **Step 1: Implement minimal held creation**

When source is public:
- create booking with `status = held`
- set `hold_expires_at = now()->addMinutes(10)`

For non-public flows:
- keep existing status behavior

- [ ] **Step 2: Run public booking tests**

Run:
```bash
php artisan test tests/Feature/Public/BookingFlowTest.php
```

Expected:
- PASS held creation expectations
- FAIL remaining availability or transition expectations

### Task 4: Make availability ignore expired held bookings

**Files:**
- Modify: `app/Services/Availability/TimedServiceAvailabilityResolver.php`
- Modify: `app/Http/Requests/Booking/BaseStoreBookingRequest.php`
- Modify: `tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php`

- [ ] **Step 1: Update blocking rules**

Treat bookings as blocking only when:
- status is `held` and `hold_expires_at` is still in the future
- status is `confirmed`
- status is `checked_in`

- [ ] **Step 2: Re-run resolver tests**

Run:
```bash
php artisan test tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php
```

Expected:
- PASS availability rules for active and expired holds

## Chunk 3: Staff Transition Rules

### Task 5: Add failing tests for confirm/cancel/expire transitions

**Files:**
- Modify: `tests/Feature/Admin/BookingManagementTest.php`
- Modify: `app/Http/Requests/Admin/TransitionBookingStatusRequest.php`

- [ ] **Step 1: Add tests**

Assert:
- `held -> confirmed` succeeds before expiry
- `held -> cancelled` succeeds
- expired held booking cannot be confirmed
- transition options include the new lifecycle states where needed

- [ ] **Step 2: Run admin booking tests to verify they fail**

Run:
```bash
php artisan test tests/Feature/Admin/BookingManagementTest.php
```

Expected:
- FAIL because request rules and manager transitions do not support held lifecycle yet

### Task 6: Implement new transition behavior

**Files:**
- Modify: `app/Services/Booking/BookingStatusManager.php`
- Modify: `app/Http/Requests/Admin/TransitionBookingStatusRequest.php`
- Modify: `app/Http/Controllers/Admin/BookingManagementController.php`

- [ ] **Step 1: Update allowed transitions**

Support:
- `held -> confirmed`
- `held -> cancelled`
- `held -> expired`

Set timestamps:
- `confirmed_at`
- `expired_at`

Reject confirm once `hold_expires_at <= now()`

- [ ] **Step 2: Ensure staff listing does not treat expired holds as actionable**

At minimum in this slice:
- exclude expired-in-runtime held bookings from the actionable transition options
- expose `hold_expires_at` in booking payload for later UI work

- [ ] **Step 3: Re-run admin booking tests**

Run:
```bash
php artisan test tests/Feature/Admin/BookingManagementTest.php
```

Expected:
- PASS for valid held transitions
- PASS for rejection of expired hold confirmation

## Chunk 4: Cleanup Command

### Task 7: Add failing command test for expiring held bookings

**Files:**
- Create or modify: `tests/Feature/Console/ExpireHeldBookingsCommandTest.php`

- [ ] **Step 1: Write command test**

Assert the command:
- finds held bookings with expired `hold_expires_at`
- updates them to `expired`
- sets `expired_at`
- leaves active held bookings untouched

- [ ] **Step 2: Run the command test**

Run:
```bash
php artisan test tests/Feature/Console/ExpireHeldBookingsCommandTest.php
```

Expected:
- FAIL because command does not exist yet

### Task 8: Implement and schedule cleanup command

**Files:**
- Create: `app/Console/Commands/ExpireHeldBookingsCommand.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Implement command**

Create a command that expires only:
- `status = held`
- `hold_expires_at <= now()`

- [ ] **Step 2: Schedule command**

Register a schedule entry in `routes/console.php` to run every minute.

- [ ] **Step 3: Re-run command test**

Run:
```bash
php artisan test tests/Feature/Console/ExpireHeldBookingsCommandTest.php
```

Expected:
- PASS

## Chunk 5: Final Verification

### Task 9: Run full slice verification

**Files:**
- No new files

- [ ] **Step 1: Run focused test suite**

Run:
```bash
php artisan test tests/Feature/Public/BookingFlowTest.php tests/Feature/Admin/BookingManagementTest.php tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php tests/Feature/Console/ExpireHeldBookingsCommandTest.php
```

Expected:
- PASS

- [ ] **Step 2: Run broader regression around booking-related flows**

Run:
```bash
php artisan test tests/Feature/Public tests/Feature/Admin/BookingManagementTest.php tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php
```

Expected:
- PASS or investigate regressions immediately

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_03_31_000001_add_hold_lifecycle_columns_to_bookings_table.php app/Models/Booking.php app/Services/Booking/BookingCreator.php app/Services/Booking/BookingStatusManager.php app/Services/Availability/TimedServiceAvailabilityResolver.php app/Http/Controllers/Admin/BookingManagementController.php app/Http/Requests/Admin/TransitionBookingStatusRequest.php app/Http/Requests/Booking/BaseStoreBookingRequest.php app/Console/Commands/ExpireHeldBookingsCommand.php routes/console.php tests/Feature/Public/BookingFlowTest.php tests/Feature/Admin/BookingManagementTest.php tests/Unit/Availability/TimedServiceAvailabilityResolverTest.php tests/Feature/Console/ExpireHeldBookingsCommandTest.php
git commit -m "Implement booking hold lifecycle"
```

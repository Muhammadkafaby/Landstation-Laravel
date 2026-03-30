# Component: Phase 8 Audit Trail for Critical Operator Actions

## Goal
Add persistent audit logging for high-risk operator actions so production usage has accountability and basic forensic traceability.

## Scope
- Add `audit_logs` table.
- Add `AuditLog` model.
- Add `AuditLogger` service.
- Log these actions only in this slice:
  - manual payment verification
  - booking status transitions
  - service session start
  - service session stop
- Keep this slice backend-only.
- No audit UI/search yet.

## Files to Create / Modify

### Create
- `database/migrations/*_create_audit_logs_table.php`
- `app/Models/AuditLog.php`
- `app/Services/Audit/AuditLogger.php`
- `tests/Feature/Audit/AuditLoggingTest.php`

### Modify
- `app/Services/Payments/ManualPaymentVerifier.php`
- `app/Services/Booking/BookingStatusManager.php`
- `app/Services/Sessions/ServiceSessionService.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- Adds one new table only.
- No route changes.
- No UI changes.

## Validation Plan
- Write failing tests first for each critical action.
- Verify red.
- Implement minimal schema/logger/service instrumentation.
- Verify green.
- Re-run relevant broader suite.

## Risks / Open Questions
- Keep payload small and structured.
- Log actor, action, auditable type/id, and lightweight context only.
- Do not leak unnecessary snapshot payloads into audit logs.

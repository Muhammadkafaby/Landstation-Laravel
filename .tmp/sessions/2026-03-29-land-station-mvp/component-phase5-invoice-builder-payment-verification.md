# Component: Phase 5 Invoice Builder and Payment Verification Foundation

## Goal
Add the first backend billing layer so completed timed sessions and submitted cafe orders can be assembled into invoices, then settled through verified manual payments.

## Scope
- Add `InvoiceBuilder` service.
- Add `ManualPaymentVerifier` service.
- Keep this slice backend-focused.
- No checkout page/controller yet.
- Reuse existing invoice/payment schema and current booking/session/order foundations.

## Files to Create / Modify

### Create
- `app/Services/Checkout/InvoiceBuilder.php`
- `app/Services/Payments/ManualPaymentVerifier.php`
- `tests/Unit/Services/InvoiceBuilderTest.php`
- `tests/Feature/Billing/PaymentVerificationTest.php`

### Modify
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- No route changes.
- Uses `invoices`, `invoice_lines`, `payments`, `service_sessions`, `orders`, and `order_items`.

## Validation Plan
- Write failing tests first for:
  - invoice totals from session + order items
  - invoice line snapshot creation
  - paid invoice rebuild rejection
  - cash verification
  - QRIS manual verification
  - overpay rejection
  - permission rejection for non-authorized verifier
- Verify red.
- Implement services only.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Keep paid invoices immutable.
- Manual QRIS is verified by cashier/staff, not gateway callback.
- Preserve snapshot-driven invoice lines rather than recalculating from mutable current data.
- Do not introduce discounts/tax logic beyond zero defaults in this slice.

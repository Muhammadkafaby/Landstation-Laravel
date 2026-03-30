# Component: Phase 5 POS Checkout Page and Controller Flow

## Goal
Add the first cashier-facing checkout screen so a completed service session and its linked cafe orders can be previewed as an invoice and settled through manual cash/QRIS payment from the web UI.

## Scope
- Add checkout show route/page for one `service_session`.
- Add checkout payment submit route.
- Reuse existing `InvoiceBuilder` and `ManualPaymentVerifier`.
- Keep this slice incremental:
  - no invoice list/index
  - no refunds/voids
  - no discount/tax editing
  - no booking-only or cafe-only checkout yet

## Files to Create / Modify

### Create
- `app/Http/Controllers/Pos/CheckoutController.php`
- `app/Http/Requests/Pos/StoreCheckoutPaymentRequest.php`
- `resources/js/Pages/Pos/Checkout/Show.jsx`
- `tests/Feature/Pos/CheckoutFlowTest.php`

### Modify
- `routes/web.php`
- `resources/js/Pages/Pos/Dashboard/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add routes:
  - `pos.checkout.show`
  - `pos.checkout.payments.store`
- Uses existing `invoices`, `invoice_lines`, `payments`, `service_sessions`, `orders`, and `payment_methods`.

## Validation Plan
- Write failing feature tests first for:
  - cashier access to checkout page
  - invoice preview build from completed session
  - successful cash payment
  - overpay rejection
  - paid state rendering after settlement
- Verify red.
- Implement thin controller/request/page.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Checkout page should avoid rebuilding invoices after payments already exist; load existing open invoice instead.
- Payment submission stays manual and must use existing payment verifier permission checks.
- Keep UI simple and operational, not polished.

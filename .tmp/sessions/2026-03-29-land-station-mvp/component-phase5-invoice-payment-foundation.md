# Component: Phase 5 Invoice and Payment Schema Foundation

## Goal
Add the first invoice and payment schema layer so checkout can later merge timed sessions and cafe orders into immutable commercial records.

## Scope
- Add schema for:
  - `payment_methods`
  - `invoices`
  - `invoice_lines`
  - `payments`
- Add baseline models and relations.
- Seed minimal payment methods for v1:
  - `cash`
  - `qris_manual`
- Keep this slice schema-first.
- Do not add invoice builder, payment verification flow, checkout UI, or reports yet.

## Files to Create / Modify

### Create
- `database/migrations/*_create_payment_methods_table.php`
- `database/migrations/*_create_invoices_table.php`
- `database/migrations/*_create_invoice_lines_table.php`
- `database/migrations/*_create_payments_table.php`
- `app/Models/PaymentMethod.php`
- `app/Models/Invoice.php`
- `app/Models/InvoiceLine.php`
- `app/Models/Payment.php`
- `database/seeders/PaymentMethodSeeder.php`
- `tests/Feature/Database/InvoicePaymentSchemaTest.php`
- `tests/Feature/Database/InvoicePaymentRelationsTest.php`

### Modify
- `database/seeders/DatabaseSeeder.php`
- `app/Models/Customer.php`
- `app/Models/Booking.php`
- `app/Models/ServiceSession.php`
- `app/Models/User.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- Adds invoice/payment domain tables only.
- No route changes.
- Existing booking/POS flows remain unchanged.

## Validation Plan
- Write failing schema and relation tests first.
- Verify red.
- Implement minimal migrations/models/seeder.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Keep all money fields as bigint integer rupiah.
- Preserve immutable line/payment payload snapshots.
- Use nullable refs so future invoice builder can support booking-only, session-only, cafe-only, or mixed invoices.

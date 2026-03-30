# Land Station MVP Master Plan

## Goal
Build the Land Station v1 web system with customer website, admin dashboard, POS, booking, per-minute billing, and QRIS manual payment.

## Architecture
- Laravel 12 monolith with Inertia React frontend.
- Domain modules split into `Public`, `Admin`, and `Pos` interfaces.
- Core services handle availability, pricing, billing, promotion, and payment verification.

## Components
1. Foundation scaffold and app shells
2. Access control and staff auth
3. Master data and settings
4. Booking and availability
5. POS timer and billing
6. Cafe ordering and checkout
7. Dashboard and reports
8. Hardening and audit

## Current Execution Slice
- Completed: Phase 1 Component 1 - module route foundation (`Public`, `Admin`, `Pos`, `Management` entrypoints)
- Completed: Phase 1 Component 2 - flexible service catalog baseline
- Delivered: schema + models + baseline seeder for `service_categories`, `services`, and `service_units`
- Completed: Phase 1 Component 3 - service pricing and booking-policy baseline
- Delivered: schema + models + seeded defaults for `service_pricing_rules` and `service_booking_policies`
- Completed: Phase 1 Component 4 - data-backed admin management read surface for seeded master data
- Delivered: `/management` summary cards and grouped category/service overview from seeded DB props
- Completed: Phase 1 Component 5 - data-backed public service catalog read surface
- Delivered: `/services` guest catalog summary with category/service/pricing readiness props
- Completed: Phase 1 Component 6 - data-backed public homepage overview
- Delivered: `/` guest overview with seeded summary counts and featured-service signals
- Completed: Phase 2 Component 1 - admin dashboard metrics read surface
- Delivered: `/dashboard` operational summary cards and category overview from seeded DB props
- Completed: Phase 2 Component 2 - service master-data CRUD foundation
- Delivered: management-protected CRUD create/update flows for service categories and services
- Completed: Phase 2 Component 3 - service unit CRUD foundation
- Delivered: management-protected CRUD create/update flows for `service_units`
- Completed: Phase 2 Component 4 - pricing rule and booking policy CRUD foundation
- Delivered: management-protected CRUD create/update flows for `service_pricing_rules` and `service_booking_policies`
- Completed: Phase 3 Component 1 - booking schema and relation foundation
- Delivered: schema + models for `customers`, `bookings`, and `service_sessions`
- Completed: Phase 3 Component 2 - availability resolver and booking validation foundation
- Delivered: reusable timed-service availability resolver with policy validation and overlap filtering
- Completed: Phase 3 Component 3 - booking create flow and public/admin booking entry
- Delivered: guest + internal booking create pages, shared validation, and booking creation service
- Completed: Phase 3 Component 4 - booking listing/management flow and status transitions
- Delivered: internal booking list plus minimal lifecycle transitions (`pending->confirmed/cancelled`, `confirmed->checked_in/cancelled/no_show`, `checked_in->completed`)
- Completed: Phase 4 Component 1 - service session start/stop foundation for POS
- Delivered: cashier-only session control with walk-in/booking-linked start and stop lifecycle
- Completed: Phase 4 Component 2 - POS cafe order schema and order flow
- Delivered: cafe product catalog foundation plus cashier order create flow linked to customer/booking/session
- Completed: Phase 5 Component 1 - invoice and payment schema foundation
- Delivered: schema + models + payment method seed data for invoices, lines, and payments
- Completed: Phase 5 Component 2 - invoice builder and payment verification foundation
- Delivered: backend invoice assembly and manual payment verification services with test coverage
- Completed: Phase 5 Component 3 - POS checkout page/controller flow
- Delivered: cashier-facing invoice preview and manual payment submission flow for completed sessions
- Completed: Phase 6 Component 1 - reporting read surface and operational summaries
- Delivered: admin-only reports page with booking/session/order/invoice/payment aggregates
- Completed: Phase 6 Component 2 - customer history and transaction history read surface
- Delivered: admin-only customer list and detail pages with booking/session/order/invoice/payment history
- Completed: Phase 6 Component 3 - transaction ledger and drill-down reporting
- Delivered: admin-only invoice-centric ledger with line-item and payment drill-down
- Completed: Phase 7 Component 1A - customer history search filter
- Delivered: GET-based search on `reports.customers.index` for name, phone, and email
- Completed: Phase 7 Component 1B - transaction ledger filters and search
- Delivered: GET-based search/filter on `reports.transactions.index` for invoice code, customer, status, and payment method
- Completed: Phase 7 Component 1C - reports summary date scope filter
- Delivered: GET-based `date_scope` filtering on `reports.index` for all/today/last_7_days windows
- Completed: Phase 7 Component 2 - transaction ledger CSV export
- Delivered: admin-only CSV export for filtered transaction ledger data
- Completed: Phase 7 Component 3 - customer history CSV export
- Delivered: admin-only CSV export for filtered customer history summaries
- Completed: Phase 7 Component 4 - reports summary CSV export
- Delivered: admin-only CSV export for summary/report aggregate metrics
- Completed: Phase 8 Component 1 - audit trail for critical operator actions
- Delivered: persistent audit logging for payment verification, booking transitions, and service session start/stop
- Completed: Phase 8 Component 2 - customer history pagination foundation
- Delivered: paginated customer history list with query preservation and unchanged export behavior
- Completed: Phase 8 Component 3 - transaction ledger pagination foundation
- Delivered: paginated transaction ledger with query preservation and unchanged export behavior
- Completed: Phase 8 Component 4 - booking management pagination foundation
- Delivered: paginated booking management list with unchanged lifecycle transition behavior
- Completed: Phase 8 Component 5 - admin audit log viewer
- Delivered: admin-only audit log reporting with filters, pagination, and CSV export
- Next: Phase 8 Component 6 - Node/tooling upgrade guidance and final hardening pass

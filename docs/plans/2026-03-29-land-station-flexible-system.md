# Land Station Flexible System Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Evolve the current Laravel 12 + Inertia React foundation into a flexible Land Station monolith that supports public website, admin dashboard, POS, and operations for cafe, billiard, PlayStation, and rental RC without hardcoding flows per business type.

**Architecture:** Keep one Laravel monolith, but split the app into explicit `Public`, `Admin`, `Pos`, and `Shared` modules. Store service definitions, units, pricing, booking rules, and POS behavior in master data tables so controllers and UI resolve behavior from configuration and snapshots instead of branching by service name.

**Tech Stack:** Laravel 12, PHP 8.2, Inertia.js, React 18, Tailwind CSS, MySQL 8, Pest.

---

## Goal

- Build one configurable system for:
  - public website
  - admin dashboard
  - POS
  - service management
- Support service categories:
  - cafe
  - billiard
  - PlayStation
  - rental RC
- Keep service behavior data-driven:
  - timed vs non-timed service
  - bookable vs walk-in only
  - unit-based vs menu-based sales
  - pricing, promos, and checkout rules
- Exclude HRD/career scope completely:
  - no job positions
  - no job applications
  - no career pages
  - no HRD email/workflows

## Scope

In scope for this plan:

- public marketing website and service discovery
- booking-ready service catalog
- internal admin master data and settings
- POS transaction flow for timed services and cafe orders
- operational data model for units, bookings, sessions, invoices, payments, and reporting
- access control expansion from current staff foundation

Out of scope for this plan version:

- mobile app
- marketplace integrations
- payroll / HR / recruitment
- online payment gateway settlement automation
- accounting/general ledger module

## Current Repo Baseline

The current repository already has the right shell for a flexible monolith:

- Backend stack present in `composer.json`
- Frontend stack present in `package.json`
- Inertia public/admin/POS shell routes in `routes/web.php`
- Middleware aliases wired in `bootstrap/app.php`
- Staff/permission model foundation in:
  - `app/Models/User.php`
  - `app/Models/Role.php`
  - `app/Models/Permission.php`
  - `database/seeders/AccessControlSeeder.php`
- Inertia shared auth capability payload in `app/Http/Middleware/HandleInertiaRequests.php`
- Initial UI shells in:
  - `resources/js/Pages/Welcome.jsx`
  - `resources/js/Pages/Dashboard.jsx`
  - `resources/js/Pages/Pos/Index.jsx`
  - `resources/js/Layouts/AuthenticatedLayout.jsx`
- Existing access tests in `tests/Feature/AccessControl/RouteAccessTest.php`

Main gap: current UI and route shells are still static foundations. The system needs domain modules, master data, transaction tables, operational services/actions, and route/controller structure that resolves behavior from the database.

## Architecture

### Target monolith shape

- Keep one Laravel app and one MySQL database.
- Separate interface layers by responsibility:
  - `app/Http/Controllers/Public`
  - `app/Http/Controllers/Admin`
  - `app/Http/Controllers/Pos`
  - `resources/js/Pages/Public`
  - `resources/js/Pages/Admin`
  - `resources/js/Pages/Pos`
- Keep controllers thin.
- Put business rules in dedicated domain services/actions under:
  - `app/Services`
  - `app/Actions`
- Use master-data tables to describe service behavior instead of hardcoded `if service === billiard` logic.

### Data-driven design rules

- `service_categories` define high-level business families: cafe, billiard, PlayStation, rental RC.
- `services` define sellable/operable offerings under a category.
- `service_units` define physical assets when a service is unit-based.
- `service_pricing_rules` define rate behavior and pricing mode.
- `service_sessions` store live/closed timed usage. Do not use `sessions` because Laravel already owns that table.
- `bookings`, `orders`, `invoices`, and `payments` store snapshots of commercial events so future config changes do not mutate history.

### Interface boundaries

- Public:
  - marketing pages
  - service listing
  - availability/search entry points
  - booking creation/status lookup
- Admin:
  - dashboard
  - master data
  - pricing/promo config
  - booking oversight
  - service unit monitoring
  - reporting
- POS:
  - start/stop service sessions
  - attach cafe orders to active customer bill
  - walk-in booking/session handling
  - invoice and payment capture

### Recommended domain modules

1. Access Control
2. Service Catalog
3. Unit & Availability Management
4. Booking
5. Service Session & Billing
6. Cafe Ordering
7. Checkout, Invoice, Payment
8. Dashboard & Reporting
9. Audit & Operational Safety

## Target Database Design

### Core tables

| Table | Purpose | Key columns / notes |
| --- | --- | --- |
| `roles` | Existing staff role table | keep current table |
| `permissions` | Existing permission catalog | expand with module permissions |
| `role_permissions` | Existing pivot | keep current table |
| `users` | Staff users now; can later support customers if needed | keep current staff fields; customer profile should be separate if public auth is added later |
| `service_categories` | Top-level business families | `code`, `name`, `description`, `is_active`, seeded with `cafe`, `billiard`, `playstation`, `rental-rc` |
| `services` | Configurable services offered by the business | `service_category_id`, `code`, `name`, `slug`, `service_type`, `billing_type`, `fulfillment_type`, `booking_mode`, `is_active`, `sort_order` |
| `service_units` | Physical rentable/playable units | `service_id`, `code`, `name`, `zone`, `status`, `capacity`, `metadata_json`, `is_bookable`, `is_active` |
| `service_operating_hours` | Weekly operating calendar per service | `service_id`, `day_of_week`, `open_time`, `close_time`, `is_closed` |
| `service_pricing_rules` | Configurable pricing | `service_id`, optional `service_unit_id`, `pricing_model`, `billing_interval_minutes`, `base_price_rupiah`, `price_per_interval_rupiah`, `minimum_charge_rupiah`, `starts_at`, `ends_at`, `priority`, `is_active` |
| `service_booking_policies` | Booking behavior per service | `service_id`, `slot_interval_minutes`, `min_duration_minutes`, `max_duration_minutes`, `lead_time_minutes`, `max_advance_days`, `requires_unit_assignment`, `walk_in_allowed`, `online_booking_allowed` |
| `customers` | Shared customer record for bookings/invoices | `name`, `phone`, `email`, `notes`, `metadata_json` |
| `bookings` | Reservation header | `booking_code`, `customer_id`, `service_id`, `service_unit_id`, `status`, `booking_source`, `start_at`, `end_at`, `duration_minutes`, `pricing_snapshot_json`, `notes`, `created_by_user_id` |
| `service_sessions` | Active/completed timed usage | `session_code`, `service_id`, `service_unit_id`, `customer_id`, optional `booking_id`, `status`, `started_at`, `ended_at`, `paused_at`, `billed_minutes`, `pricing_snapshot_json`, `started_by_user_id`, `closed_by_user_id` |
| `product_categories` | Cafe catalog grouping | `code`, `name`, `is_active`, `sort_order` |
| `products` | Cafe sellable items | `product_category_id`, `sku`, `name`, `product_type`, `price_rupiah`, `cost_rupiah`, `is_active`, `metadata_json` |
| `orders` | POS order header | `order_code`, `customer_id`, optional `booking_id`, optional `service_session_id`, `status`, `ordered_at`, `created_by_user_id` |
| `order_items` | Cafe item lines | `order_id`, `product_id`, `qty`, `unit_price_rupiah`, `subtotal_rupiah`, `item_snapshot_json`, `notes` |
| `invoices` | Commercial settlement header | `invoice_code`, `customer_id`, optional `booking_id`, optional `service_session_id`, `status`, `subtotal_rupiah`, `discount_rupiah`, `tax_rupiah`, `grand_total_rupiah`, `issued_at`, `closed_at`, `created_by_user_id` |
| `invoice_lines` | Mixed service/cafe billing lines | `invoice_id`, `line_type`, `reference_type`, `reference_id`, `description`, `qty`, `unit_price_rupiah`, `subtotal_rupiah`, `snapshot_json` |
| `payments` | Payment records | `invoice_id`, `payment_method_code`, `status`, `amount_rupiah`, `paid_at`, `reference_number`, `verified_by_user_id`, `notes`, `payload_json` |

### Recommended supporting tables

| Table | Why it matters |
| --- | --- |
| `payment_methods` | configurable cash / QRIS manual / future methods |
| `promotions` | service-wide or product-wide promo definitions |
| `promotion_targets` | promo-to-service/product linkage |
| `promotion_redemptions` | preserve applied promo history |
| `service_unit_status_logs` | audit unit moves, maintenance, occupancy transitions |
| `booking_status_logs` | track reservation lifecycle changes |
| `service_session_events` | preserve start/pause/resume/stop timeline |
| `invoice_adjustments` | explicit override/discount/surcharge history |
| `audit_logs` | cross-module audit for pricing override, payment verification, unit move |
| `settings` | key-value business settings for branding, invoice numbering, tax flags |
| `media_assets` | optional uploaded service/product images for public website |

### Key modeling decisions

- Use integer rupiah fields everywhere for money.
- Keep pricing snapshots on `bookings`, `service_sessions`, `order_items`, and `invoice_lines`.
- Model timed usage only through `service_sessions`.
- Use `service_type`/`billing_type` fields to avoid branching by category name.
- Let cafe sales exist without service session; let service session exist without cafe sale; merge both in invoice.
- Prefer nullable foreign keys plus immutable snapshots over recalculating historical prices from current master data.

### Suggested enums / constant sets

- `services.service_type`:
  - `timed_unit`
  - `timed_open_area`
  - `menu_only`
- `services.billing_type`:
  - `per_minute`
  - `flat`
  - `manual`
- `service_units.status`:
  - `available`
  - `occupied`
  - `reserved`
  - `maintenance`
  - `inactive`
- `bookings.status`:
  - `pending`
  - `confirmed`
  - `checked_in`
  - `completed`
  - `cancelled`
  - `no_show`
- `service_sessions.status`:
  - `active`
  - `paused`
  - `completed`
  - `cancelled`
- `orders.status`:
  - `draft`
  - `submitted`
  - `completed`
  - `cancelled`
- `invoices.status`:
  - `draft`
  - `open`
  - `paid`
  - `void`
- `payments.status`:
  - `pending`
  - `verified`
  - `failed`
  - `void`

## Files to Create / Modify

### Existing files already in play

- Modify: `routes/web.php`
- Modify: `bootstrap/app.php`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Modify: `app/Models/User.php`
- Modify: `app/Models/Role.php`
- Modify: `app/Models/Permission.php`
- Modify: `resources/js/Pages/Welcome.jsx`
- Modify: `resources/js/Pages/Dashboard.jsx`
- Modify: `resources/js/Pages/Pos/Index.jsx`
- Modify: `resources/js/Layouts/AuthenticatedLayout.jsx`
- Modify: `database/seeders/AccessControlSeeder.php`
- Modify: `tests/Feature/AccessControl/RouteAccessTest.php`

### New backend structure

- Create: `app/Http/Controllers/Public/HomeController.php`
- Create: `app/Http/Controllers/Public/ServiceCatalogController.php`
- Create: `app/Http/Controllers/Public/BookingController.php`
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `app/Http/Controllers/Admin/ServiceCategoryController.php`
- Create: `app/Http/Controllers/Admin/ServiceController.php`
- Create: `app/Http/Controllers/Admin/ServiceUnitController.php`
- Create: `app/Http/Controllers/Admin/ProductController.php`
- Create: `app/Http/Controllers/Admin/BookingManagementController.php`
- Create: `app/Http/Controllers/Admin/ReportController.php`
- Create: `app/Http/Controllers/Pos/SessionController.php`
- Create: `app/Http/Controllers/Pos/OrderController.php`
- Create: `app/Http/Controllers/Pos/CheckoutController.php`
- Create: `app/Http/Requests/Admin/...`
- Create: `app/Http/Requests/Pos/...`
- Create: `app/Models/ServiceCategory.php`
- Create: `app/Models/Service.php`
- Create: `app/Models/ServiceUnit.php`
- Create: `app/Models/ServiceOperatingHour.php`
- Create: `app/Models/ServicePricingRule.php`
- Create: `app/Models/ServiceBookingPolicy.php`
- Create: `app/Models/Customer.php`
- Create: `app/Models/Booking.php`
- Create: `app/Models/ServiceSession.php`
- Create: `app/Models/ProductCategory.php`
- Create: `app/Models/Product.php`
- Create: `app/Models/Order.php`
- Create: `app/Models/OrderItem.php`
- Create: `app/Models/Invoice.php`
- Create: `app/Models/InvoiceLine.php`
- Create: `app/Models/Payment.php`
- Create: `app/Models/PaymentMethod.php`
- Create: `app/Services/Availability/AvailabilityResolver.php`
- Create: `app/Services/Pricing/PricingResolver.php`
- Create: `app/Services/Booking/BookingService.php`
- Create: `app/Services/Sessions/ServiceSessionService.php`
- Create: `app/Services/Checkout/InvoiceBuilder.php`
- Create: `app/Services/Payments/ManualPaymentVerifier.php`
- Create: `app/Actions/...` for narrow write operations if service classes become large

### New frontend structure

- Create: `resources/js/Pages/Public/Home.jsx`
- Create: `resources/js/Pages/Public/Services/Index.jsx`
- Create: `resources/js/Pages/Public/Bookings/Create.jsx`
- Create: `resources/js/Pages/Public/Bookings/Show.jsx`
- Create: `resources/js/Pages/Admin/Dashboard/Index.jsx`
- Create: `resources/js/Pages/Admin/Services/Index.jsx`
- Create: `resources/js/Pages/Admin/Services/Form.jsx`
- Create: `resources/js/Pages/Admin/Units/Index.jsx`
- Create: `resources/js/Pages/Admin/Products/Index.jsx`
- Create: `resources/js/Pages/Admin/Bookings/Index.jsx`
- Create: `resources/js/Pages/Admin/Reports/Index.jsx`
- Create: `resources/js/Pages/Pos/Sessions/Index.jsx`
- Create: `resources/js/Pages/Pos/Orders/Index.jsx`
- Create: `resources/js/Pages/Pos/Checkout/Show.jsx`
- Create: `resources/js/Components/...` shared cards, filters, status badges, tables

### New database files

- Create migrations for every new table above
- Create/update factories as models are introduced
- Create seeders:
  - `database/seeders/ServiceCatalogSeeder.php`
  - `database/seeders/ProductCatalogSeeder.php`
  - `database/seeders/PaymentMethodSeeder.php`
  - `database/seeders/SettingsSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

### New tests

- Create: `tests/Feature/Public/...`
- Create: `tests/Feature/Admin/...`
- Create: `tests/Feature/Pos/...`
- Create: `tests/Feature/Booking/...`
- Create: `tests/Feature/Billing/...`
- Create: `tests/Unit/Services/AvailabilityResolverTest.php`
- Create: `tests/Unit/Services/PricingResolverTest.php`
- Create: `tests/Unit/Services/InvoiceBuilderTest.php`

## Phased Implementation Roadmap

### Phase 1 — Domain foundation and route refactor

Goal: move from static shell routes/pages to explicit Public/Admin/Pos modules and seed flexible service master data.

### Phase 2 — Flexible master data and admin management

Goal: make service categories, services, units, pricing, products, and payment methods editable through admin.

### Phase 3 — Booking and availability

Goal: introduce customer records, booking policies, availability rules, and public/internal booking flows.

### Phase 4 — POS service sessions and cafe ordering

Goal: allow cashiers to run timed services through `service_sessions`, attach cafe orders, and monitor live operations.

### Phase 5 — Checkout, invoices, payments, and reporting

Goal: finalize bill generation, manual QRIS/cash verification, and operational reporting.

### Phase 6 — Hardening, audit, and rollout safety

Goal: protect business invariants, add auditability, and make the monolith safe to extend.

## Phase-by-Phase Execution Plan

## Phase 1 — Domain foundation and route refactor

### Goal

- Replace route closures with controllers.
- Split pages by `Public`, `Admin`, and `Pos` folders.
- Introduce seed data for flexible service categories and baseline permissions.

### Data and route impact

- Adds baseline service catalog tables.
- Expands permission list beyond current admin/POS access.
- Keeps current login flow and current staff seeders working.

### Tasks

#### Task 1.1: Refactor route structure into interface modules

**Files**
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/Public/HomeController.php`
- Create: `app/Http/Controllers/Admin/DashboardController.php`
- Create: `app/Http/Controllers/Pos/DashboardController.php` or keep `SessionController.php` as initial landing

**Work**
- Replace Inertia route closures with controller actions.
- Keep existing route names where possible:
  - `dashboard`
  - `pos.index`
- Add named public service routes for later expansion.

**Tests**
- Update: `tests/Feature/AccessControl/RouteAccessTest.php`
- Add route smoke tests for `/` and `/services`.

**Verification**
- `php artisan route:list`
- `php artisan test tests/Feature/AccessControl/RouteAccessTest.php`

#### Task 1.2: Split frontend pages into explicit module folders

**Files**
- Create: `resources/js/Pages/Public/Home.jsx`
- Create: `resources/js/Pages/Admin/Dashboard/Index.jsx`
- Create: `resources/js/Pages/Pos/Sessions/Index.jsx`
- Modify or retire later: `resources/js/Pages/Welcome.jsx`, `resources/js/Pages/Dashboard.jsx`, `resources/js/Pages/Pos/Index.jsx`
- Modify: `resources/js/Layouts/AuthenticatedLayout.jsx`

**Work**
- Move current shell content into module-based page locations.
- Update navigation labels and route awareness for future admin subsections.
- Keep auth capability-based nav rendering.

**Tests**
- Feature tests assert Inertia component names.

**Verification**
- `php artisan test tests/Feature/AccessControl/RouteAccessTest.php`
- `npm run build`

#### Task 1.3: Add flexible service catalog schema baseline

**Files**
- Create migrations for:
  - `service_categories`
  - `services`
  - `service_units`
  - `service_operating_hours`
  - `service_pricing_rules`
  - `service_booking_policies`
- Create models matching the tables

**Work**
- Encode service behavior as columns, not class-per-service.
- Ensure `services` can represent both timed and cafe-like offerings.
- Index `code`, `slug`, `status`, foreign keys, and active windows.

**Tests**
- Create migration/schema smoke test.
- Create model relation tests.

**Verification**
- `php artisan migrate:fresh --seed`
- `php artisan test tests/Feature tests/Unit`

#### Task 1.4: Seed baseline services and permissions

**Files**
- Modify: `database/seeders/AccessControlSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `database/seeders/ServiceCatalogSeeder.php`

**Work**
- Seed service categories:
  - `cafe`
  - `billiard`
  - `playstation`
  - `rental-rc`
- Seed example services and units so admin/POS screens have usable data.
- Expand permissions for service management, pricing, reports, bookings, sessions, invoices.

**Tests**
- Seeder tests for expected service category codes.
- Access control tests for new permissions.

**Verification**
- `php artisan db:seed --class=ServiceCatalogSeeder`
- `php artisan test`

### Milestone checks

- Module route structure exists.
- Baseline service master data exists.
- Frontend shell uses module-based page paths.

## Phase 2 — Flexible master data and admin management

### Goal

- Build admin CRUD for service categories, services, units, pricing rules, products, and payment methods.

### Data and route impact

- Introduces editable master data.
- Establishes admin navigation and validation patterns.

### Tasks

#### Task 2.1: Admin service catalog CRUD

**Files**
- Create: `app/Http/Controllers/Admin/ServiceCategoryController.php`
- Create: `app/Http/Controllers/Admin/ServiceController.php`
- Create: `app/Http/Requests/Admin/StoreServiceCategoryRequest.php`
- Create: `app/Http/Requests/Admin/StoreServiceRequest.php`
- Create: `app/Http/Requests/Admin/UpdateServiceRequest.php`
- Create: `resources/js/Pages/Admin/Services/Index.jsx`
- Create: `resources/js/Pages/Admin/Services/Form.jsx`
- Modify: `routes/web.php`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`

**Work**
- Add index/create/edit/update flows.
- Show fields that drive flexibility:
  - category
  - service type
  - billing type
  - booking mode
  - active state
- Add permission guard, e.g. `manage-services` or `manage-master-data`.

**Tests**
- Feature tests for authorized CRUD.
- Feature tests for forbidden access for cashier.
- Validation tests for duplicate `code` / `slug`.

**Verification**
- `php artisan test tests/Feature/Admin/ServiceCatalogTest.php`
- `npm run build`

#### Task 2.2: Admin unit and operating rules CRUD

**Files**
- Create: `app/Http/Controllers/Admin/ServiceUnitController.php`
- Create requests for unit CRUD
- Create pages under `resources/js/Pages/Admin/Units/`
- Create supporting components for unit status and assignment

**Work**
- Manage billiard tables, PS units, RC units, and any future unitized service.
- Keep cafe services allowed to have zero units.
- Persist `is_bookable`, `status`, `capacity`, `zone`, metadata.

**Tests**
- Feature tests for unit CRUD.
- Unit tests for service-unit relation and filtering.

**Verification**
- `php artisan test tests/Feature/Admin/ServiceUnitManagementTest.php`

#### Task 2.3: Admin pricing and booking policy CRUD

**Files**
- Create: admin pricing/policy controllers or nested actions under service controller
- Create pages/components for pricing and booking policy forms
- Create request objects for pricing validation

**Work**
- Allow multiple pricing rules over time.
- Validate non-overlapping active date windows where required.
- Support service-wide and per-unit pricing overrides.
- Store minute intervals and minimum charges explicitly.

**Tests**
- Unit tests for pricing conflict validation.
- Feature tests for create/update/delete pricing rules.

**Verification**
- `php artisan test tests/Unit/Services/PricingResolverTest.php tests/Feature/Admin/ServicePricingTest.php`

#### Task 2.4: Cafe product and payment method master data

**Files**
- Create: `app/Models/ProductCategory.php`
- Create: `app/Models/Product.php`
- Create: `app/Models/PaymentMethod.php`
- Create migrations for product/payment tables
- Create: `app/Http/Controllers/Admin/ProductController.php`
- Create pages under `resources/js/Pages/Admin/Products/`
- Create seeder: `database/seeders/ProductCatalogSeeder.php`
- Create seeder: `database/seeders/PaymentMethodSeeder.php`

**Work**
- Separate cafe items from timed services.
- Seed cash and manual QRIS payment methods.
- Prepare product catalog for POS order entry.

**Tests**
- Feature CRUD tests.
- Seeder tests.

**Verification**
- `php artisan migrate:fresh --seed`
- `php artisan test`

### Milestone checks

- Admin can maintain all master data without code changes.
- New business types can be introduced by configuration first, logic second.

## Phase 3 — Booking and availability

### Goal

- Introduce customer records, booking lifecycle, and availability resolution for bookable services.

### Data and route impact

- Adds `customers` and `bookings`.
- Uses booking policies and unit status to calculate availability.

### Tasks

#### Task 3.1: Customer and booking schema

**Files**
- Create migrations for:
  - `customers`
  - `bookings`
  - `booking_status_logs`
- Create: `app/Models/Customer.php`
- Create: `app/Models/Booking.php`

**Work**
- Support both public-origin and staff-origin bookings.
- Store booking status logs for auditability.
- Store pricing snapshot at booking time if pricing is held on reservation.

**Tests**
- Model relationship tests.
- Booking creation feature tests.

**Verification**
- `php artisan test tests/Feature/Booking/BookingCreationTest.php`

#### Task 3.2: Availability service

**Files**
- Create: `app/Services/Availability/AvailabilityResolver.php`
- Create: `tests/Unit/Services/AvailabilityResolverTest.php`

**Work**
- Resolve availability from:
  - operating hours
  - booking policy
  - unit activity status
  - overlapping bookings
  - active service sessions
- Make output generic so public site and admin/POS can reuse the same resolver.

**Tests**
- overlapping booking rejection
- maintenance unit exclusion
- non-bookable service behavior
- slot generation correctness

**Verification**
- `php artisan test tests/Unit/Services/AvailabilityResolverTest.php`

#### Task 3.3: Public booking flow

**Files**
- Create: `app/Http/Controllers/Public/ServiceCatalogController.php`
- Create: `app/Http/Controllers/Public/BookingController.php`
- Create public pages under `resources/js/Pages/Public/Services` and `resources/js/Pages/Public/Bookings`
- Modify: `routes/web.php`

**Work**
- Show data from seeded services instead of static arrays in `resources/js/Pages/Welcome.jsx`.
- Create service detail + booking entry UI.
- Allow only configured services to be booked online.

**Tests**
- Public feature tests for service list and booking submission.
- Validation tests for lead time and closed hours.

**Verification**
- `php artisan test tests/Feature/Public/ServiceCatalogTest.php tests/Feature/Public/BookingFlowTest.php`
- `npm run build`

#### Task 3.4: Admin booking management

**Files**
- Create: `app/Http/Controllers/Admin/BookingManagementController.php`
- Create: `resources/js/Pages/Admin/Bookings/Index.jsx`
- Create related filter/table components

**Work**
- Allow confirm, cancel, check-in, complete, no-show transitions.
- Guard invalid lifecycle jumps.
- Surface booking source and payment/session linkage.

**Tests**
- Feature tests for valid/invalid transitions.

**Verification**
- `php artisan test tests/Feature/Admin/BookingManagementTest.php`

### Milestone checks

- Bookings are no longer hardcoded to a single business flow.
- Public website is data-backed.

## Phase 4 — POS service sessions and cafe ordering

### Goal

- Run timed service operations through flexible `service_sessions` and attach cafe orders to the same customer bill.

### Data and route impact

- Adds `service_sessions`, `service_session_events`, `orders`, and `order_items`.

### Tasks

#### Task 4.1: Service session schema and lifecycle service

**Files**
- Create migrations for:
  - `service_sessions`
  - `service_session_events`
- Create: `app/Models/ServiceSession.php`
- Create: `app/Services/Sessions/ServiceSessionService.php`
- Create: `tests/Feature/Pos/ServiceSessionLifecycleTest.php`

**Work**
- Implement start/pause/resume/stop.
- Persist pricing snapshot on session start.
- Prevent overlapping active sessions on same unit.
- Support walk-in sessions without prior booking.

**Tests**
- active overlap rejection
- start-time pricing snapshot
- pause/resume event logging
- stop closes session cleanly

**Verification**
- `php artisan test tests/Feature/Pos/ServiceSessionLifecycleTest.php`

#### Task 4.2: POS session UI

**Files**
- Create: `app/Http/Controllers/Pos/SessionController.php`
- Create: `resources/js/Pages/Pos/Sessions/Index.jsx`
- Create shared session cards/tables/components
- Modify: `resources/js/Layouts/AuthenticatedLayout.jsx`

**Work**
- Show live service units grouped by service.
- Allow cashier actions against active sessions.
- Surface elapsed time, projected total, and occupancy status.

**Tests**
- Feature tests for cashier access and action authorization.

**Verification**
- `php artisan test tests/Feature/Pos/SessionControllerTest.php`
- `npm run build`

#### Task 4.3: Cafe order schema and order flow

**Files**
- Create migrations for:
  - `orders`
  - `order_items`
- Create models and controller:
  - `app/Models/Order.php`
  - `app/Models/OrderItem.php`
  - `app/Http/Controllers/Pos/OrderController.php`
- Create: `resources/js/Pages/Pos/Orders/Index.jsx`

**Work**
- Draft and submit cafe orders.
- Allow order linkage to active session or direct customer invoice.
- Snapshot product price/name into line items.

**Tests**
- Feature tests for order creation, update, cancellation.
- Validation for inactive products.

**Verification**
- `php artisan test tests/Feature/Pos/CafeOrderFlowTest.php`

### Milestone checks

- Timed services and cafe orders are both operational in POS.
- Session logic is generic across PlayStation, billiard, and rental RC.

## Phase 5 — Checkout, invoices, payments, and reporting

### Goal

- Merge service sessions and cafe orders into invoices and payments with reporting-ready records.

### Data and route impact

- Adds `invoices`, `invoice_lines`, `payments`, optional `promotions`, and reporting queries.

### Tasks

#### Task 5.1: Invoice and payment schema

**Files**
- Create migrations for:
  - `invoices`
  - `invoice_lines`
  - `payments`
  - `payment_methods` if not yet created in phase 2
  - optional `invoice_adjustments`
- Create corresponding models

**Work**
- Keep invoice header immutable after paid except future explicit void/reversal flow.
- Allow invoice from:
  - booking only
  - service session only
  - cafe order only
  - mixed session + cafe order

**Tests**
- Model and relation tests.

**Verification**
- `php artisan test tests/Feature/Billing/InvoiceSchemaTest.php`

#### Task 5.2: Invoice builder and payment verification services

**Files**
- Create: `app/Services/Checkout/InvoiceBuilder.php`
- Create: `app/Services/Payments/ManualPaymentVerifier.php`
- Create: `tests/Unit/Services/InvoiceBuilderTest.php`
- Create: `tests/Feature/Billing/PaymentVerificationTest.php`

**Work**
- Build invoice lines from session duration snapshots and order items.
- Apply discounts/promos if present.
- Support cash and manual QRIS verification.
- Record verifier user and payment reference.

**Tests**
- invoice totals from mixed sources
- payment cannot exceed remaining total without explicit overpay rule
- paid invoices become immutable

**Verification**
- `php artisan test tests/Unit/Services/InvoiceBuilderTest.php tests/Feature/Billing/PaymentVerificationTest.php`

#### Task 5.3: POS checkout UI

**Files**
- Create: `app/Http/Controllers/Pos/CheckoutController.php`
- Create: `resources/js/Pages/Pos/Checkout/Show.jsx`

**Work**
- Show bill preview with service lines and cafe lines.
- Capture payment method, amount, reference number, notes.
- Provide clear final paid state.

**Tests**
- Feature tests for checkout happy path and invalid payment path.

**Verification**
- `php artisan test tests/Feature/Pos/CheckoutFlowTest.php`
- `npm run build`

#### Task 5.4: Admin dashboard and reporting

**Files**
- Create: `app/Http/Controllers/Admin/ReportController.php`
- Create: `resources/js/Pages/Admin/Reports/Index.jsx`
- Modify/Create: `resources/js/Pages/Admin/Dashboard/Index.jsx`

**Work**
- Build first reports from invoices, payments, bookings, service sessions.
- Start with operational metrics:
  - revenue by service category
  - active sessions
  - occupancy/utilization
  - booking conversion
  - payment method split

**Tests**
- Feature tests for report page access.
- Query/service unit tests for aggregated values.

**Verification**
- `php artisan test tests/Feature/Admin/ReportsTest.php`

### Milestone checks

- End-to-end commercial flow works.
- Reporting reads from normalized operational tables, not UI-only state.

## Phase 6 — Hardening, audit, and rollout safety

### Goal

- Add missing invariants, audit trails, settings, and rollout safety before broader use.

### Data and route impact

- Adds logs and settings tables.
- Tightens authorization and state transitions.

### Tasks

#### Task 6.1: Audit logs and status logs

**Files**
- Create migrations for:
  - `audit_logs`
  - `service_unit_status_logs`
- Create models/services/listeners as needed

**Work**
- Log pricing overrides, payment verification, booking status changes, unit status moves, session cancellation.

**Tests**
- Feature tests asserting audit entries are written on critical actions.

**Verification**
- `php artisan test tests/Feature/Audit/AuditLoggingTest.php`

#### Task 6.2: Settings and numbering infrastructure

**Files**
- Create: `settings` migration and model
- Create: `database/seeders/SettingsSeeder.php`
- Create service for invoice/booking/session code generation

**Work**
- Centralize invoice prefixes, tax flags, receipt footer, business profile, and feature toggles.

**Tests**
- Unit tests for code generation uniqueness and settings fallback.

**Verification**
- `php artisan test tests/Unit/Settings/SettingsResolverTest.php`

#### Task 6.3: Authorization hardening and navigation cleanup

**Files**
- Modify: `app/Models/Permission.php`
- Modify: `database/seeders/AccessControlSeeder.php`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Modify: `resources/js/Layouts/AuthenticatedLayout.jsx`
- Modify: route/controller middleware across admin/POS modules

**Work**
- Replace broad permissions with module-specific permissions where needed.
- Ensure nav visibility matches route authorization.
- Keep current `staff` gate model simple; do not introduce multi-role user complexity yet.

**Tests**
- Expanded access matrix tests.

**Verification**
- `php artisan test tests/Feature/AccessControl`

#### Task 6.4: Rollout checklist and fixture quality

**Files**
- Modify/add factories and seeders for representative demo data
- Optionally create docs runbook later under `docs/`

**Work**
- Seed realistic categories, services, units, products, and sample transactions.
- Make QA verification repeatable.

**Tests**
- Full suite run.

**Verification**
- `php artisan migrate:fresh --seed`
- `php artisan test`
- `npm run build`

### Milestone checks

- Critical business actions are auditable.
- Authorization, pricing, and session invariants are protected.

## Suggested Tests by Phase

| Phase | High-value tests |
| --- | --- |
| 1 | access matrix, route smoke, Inertia component assertions, seeder assertions |
| 2 | admin CRUD authorization, validation, duplicate code/slug checks, pricing rule overlap checks |
| 3 | availability resolver unit tests, booking lifecycle feature tests, public booking validation |
| 4 | service session lifecycle tests, overlap prevention, order item snapshot tests, cashier authorization |
| 5 | invoice builder totals, payment verification, immutable paid invoice behavior, report access tests |
| 6 | audit logging, code generation uniqueness, full access regression, full seed/build/test smoke |

## Verification Commands by Milestone

### After Phase 1

- `php artisan route:list`
- `php artisan migrate:fresh --seed`
- `php artisan test tests/Feature/AccessControl/RouteAccessTest.php`
- `npm run build`

### After Phase 2

- `php artisan migrate:fresh --seed`
- `php artisan test tests/Feature/Admin tests/Unit/Services/PricingResolverTest.php`
- `npm run build`

### After Phase 3

- `php artisan test tests/Feature/Public tests/Feature/Booking tests/Unit/Services/AvailabilityResolverTest.php`
- `npm run build`

### After Phase 4

- `php artisan test tests/Feature/Pos tests/Unit/Services`
- `npm run build`

### After Phase 5

- `php artisan test tests/Feature/Billing tests/Feature/Admin/ReportsTest.php tests/Unit/Services/InvoiceBuilderTest.php`
- `npm run build`

### After Phase 6 / final milestone

- `php artisan migrate:fresh --seed`
- `php artisan test`
- `npm run build`

## Risks / Migration Notes / Sequencing Notes

### Risks

- Current public page content in `resources/js/Pages/Welcome.jsx` is static; if public pages are rewritten too late, admin/POS may advance while marketing/booking stays disconnected.
- If pricing rules are not snapshotted early, invoice correctness will drift when prices change.
- If `service_sessions` lifecycle rules are implemented before unit/booking conflict logic, overlapping usage bugs will be expensive to clean later.
- Permission growth can become messy if codes are added ad hoc instead of grouped by module.

### Migration notes

- Do not rename Laravel's `sessions` table; introduce business usage table only as `service_sessions`.
- Preserve current access-control foundation and expand from it; do not rewrite auth from scratch.
- Convert route closures to controllers before adding large modules so file ownership and tests stay clear.
- Replace static page arrays with database-backed props incrementally, not all at once.

### Sequencing notes

- Phase 1 before all else: domain tables and route/controller structure unblock every later phase.
- Phase 2 before 3 and 4: booking and POS both depend on configurable services, units, pricing, and products.
- Phase 3 before 4 for booking-linked session check-in support, but walk-in POS session support can be built in parallel after `services`/`service_units`/`service_pricing_rules` are ready.
- Phase 5 should wait until session and order flows produce stable source records.
- Phase 6 should be treated as required, not optional polish, because payment verification and operational overrides need audit trails.

## Recommended first execution slice

If continuing immediately, start with this narrow slice:

1. Refactor `routes/web.php` to controller-based Public/Admin/Pos routes.
2. Move current Inertia pages into `resources/js/Pages/Public`, `resources/js/Pages/Admin`, and `resources/js/Pages/Pos`.
3. Add migrations/models/seeders for `service_categories`, `services`, and `service_units`.
4. Expand access permissions only enough to protect the upcoming admin service master-data screens.
5. Add smoke tests and seeder assertions before moving into CRUD.

## Handoff summary

- Build flexibility in data model first.
- Keep UI and route structure aligned with `Public`, `Admin`, `Pos` modules.
- Keep service logic generic around `service_type`, `billing_type`, and snapshots.
- Keep historical commercial data immutable.
- Do not introduce HRD/career scope.
- Always use `service_sessions` for timed business operations.

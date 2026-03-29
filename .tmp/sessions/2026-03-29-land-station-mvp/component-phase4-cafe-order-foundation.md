# Component: Phase 4 Cafe Order Schema and POS Order Flow Foundation

## Goal
Add the first cafe ordering foundation so cashiers can create cafe orders linked to a customer and optionally to a booking or service session, without yet introducing invoice/checkout coupling.

## Scope
- Add schema for:
  - `product_categories`
  - `products`
  - `orders`
  - `order_items`
- Add baseline product catalog seed data sufficient for POS order tests.
- Add POS order create page and store flow.
- Persist item snapshots and integer rupiah totals.
- Keep this slice create-only; no update/cancel/checkout yet.

## Files to Create / Modify

### Create
- `database/migrations/*_create_product_categories_table.php`
- `database/migrations/*_create_products_table.php`
- `database/migrations/*_create_orders_table.php`
- `database/migrations/*_create_order_items_table.php`
- `app/Models/ProductCategory.php`
- `app/Models/Product.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `database/seeders/ProductCatalogSeeder.php`
- `app/Http/Controllers/Pos/OrderController.php`
- `app/Http/Requests/Pos/StoreOrderRequest.php`
- `app/Services/Orders/PosOrderService.php`
- `resources/js/Pages/Pos/Orders/Index.jsx`
- `tests/Feature/Database/CafeOrderSchemaTest.php`
- `tests/Feature/Database/CafeOrderRelationsTest.php`
- `tests/Feature/Pos/CafeOrderFlowTest.php`

### Modify
- `database/seeders/DatabaseSeeder.php`
- `app/Models/Customer.php`
- `app/Models/Booking.php`
- `app/Models/ServiceSession.php`
- `routes/web.php`
- `resources/js/Pages/Pos/Dashboard/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- Adds product/order tables.
- Adds POS routes:
  - `pos.orders.index`
  - `pos.orders.store`
- No invoice/checkout changes yet.

## Validation Plan
- Write failing schema, relation, and POS order feature tests first.
- Verify red.
- Implement minimal migrations/models/seeder/controller/request/service/page.
- Verify green.
- Re-run broader suite.

## Risks / Open Questions
- Product pricing must be snapshotted at order time.
- Orders linked to a session/booking should remain customer-consistent.
- Keep order status minimal in this slice.

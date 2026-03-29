# Component: Phase 2 Service Catalog CRUD Foundation

## Goal
Introduce the first admin CRUD surface for the flexible service foundation so admin users can create and update service categories and services from the web UI instead of relying only on seed data.

## Scope
- Keep current auth, route names, and permission model intact.
- Reuse `manage-master-data` for access control.
- Add create/update flows for:
  - `service_categories`
  - `services`
- Add admin page for service catalog management.
- No delete flow yet.
- No unit/pricing/booking-policy CRUD yet.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Admin/ServiceCategoryController.php`
- `app/Http/Controllers/Admin/ServiceController.php`
- `app/Http/Requests/Admin/StoreServiceCategoryRequest.php`
- `app/Http/Requests/Admin/UpdateServiceCategoryRequest.php`
- `app/Http/Requests/Admin/StoreServiceRequest.php`
- `app/Http/Requests/Admin/UpdateServiceRequest.php`
- `resources/js/Pages/Admin/Services/Index.jsx`
- `resources/js/Pages/Admin/Services/Form.jsx`
- `tests/Feature/Admin/ServiceCatalogTest.php`

### Modify
- `routes/web.php`
- `resources/js/Pages/Admin/Management/Index.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add management-protected routes for service categories and services.
- Keep `management.index` and `dashboard` intact.

## Validation Plan
- Write failing feature tests for authorized access, forbidden cashier access, validation, create, and update flows.
- Verify red.
- Implement minimal controllers, requests, routes, and Inertia forms.
- Verify green with targeted tests.
- Re-run build and broader auth/access/public/database suite.

## Risks / Open Questions
- Avoid delete while units and pricing already reference services.
- Keep controllers thin; validation belongs in FormRequests.
- Service code and slug uniqueness must be preserved on create and update.
- UI can stay simple and form-heavy in this slice; polish can come later.

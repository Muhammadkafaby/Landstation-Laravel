# Component: Phase 1 Module Route Foundation

## Goal
Refactor the current route closures and flat Inertia page paths into explicit module-based controller routes for `Public`, `Admin`, `Pos`, and the internal management entrypoint, while preserving the existing auth and permission contract.

## Scope
- Replace closure-based routes with controller actions.
- Keep existing route names stable where already used:
  - `dashboard`
  - `pos.index`
- Add a management landing route for master-data operations:
  - `management.index`
- Move page entrypoints to module folders:
  - `Public/*`
  - `Admin/*`
  - `Pos/*`
- Keep the current capability-driven navigation contract working.
- Do not add business schema yet.
- Do not change login/auth flow beyond preserving redirects and route compatibility.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Public/HomeController.php`
- `app/Http/Controllers/Public/ServiceController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/ManagementController.php`
- `app/Http/Controllers/Pos/DashboardController.php`
- `resources/js/Pages/Public/Home.jsx`
- `resources/js/Pages/Public/Services/Index.jsx`
- `resources/js/Pages/Admin/Dashboard/Index.jsx`
- `resources/js/Pages/Admin/Management/Index.jsx`
- `resources/js/Pages/Pos/Dashboard/Index.jsx`
- `tests/Feature/Public/PublicRouteSmokeTest.php`

### Modify
- `routes/web.php`
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `tests/Feature/AccessControl/RouteAccessTest.php`

## Data and Route Impact
- No new database tables in this component.
- Public routes become controller-based and explicitly module-backed.
- Internal route map becomes:
  - `/dashboard` -> admin dashboard
  - `/pos` -> POS dashboard
  - `/management` -> management landing
- `management.index` should require existing `manage-master-data` capability.
- Route names used by auth redirect logic must remain valid.

## Validation Plan
- Write failing feature tests first for:
  - public homepage route
  - public services route
  - admin dashboard access
  - POS access
  - management access for admin/super admin and forbidden for cashier
  - expected Inertia component names after module refactor
- Verify red.
- Implement minimal route/controller/page changes.
- Verify green with:
  - `php artisan test tests/Feature/AccessControl/RouteAccessTest.php tests/Feature/Public/PublicRouteSmokeTest.php`
  - `php artisan route:list`
  - `npm run build`

## Risks / Open Questions
- `User::defaultLandingRouteName()` and auth login redirect depend on `dashboard` and `pos.index`; they must keep working.
- `AuthenticatedLayout` currently only shows `Admin` and `POS`; adding `Management` must stay aligned with capability flags from `HandleInertiaRequests`.
- Page path migration must match Inertia resolver pattern in `resources/js/app.jsx`.
- Current placeholder pages should be replaced by module paths without breaking navigation.

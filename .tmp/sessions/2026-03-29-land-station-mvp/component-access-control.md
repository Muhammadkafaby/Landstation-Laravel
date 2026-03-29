# Component: Access Control and Staff Auth Refinement

## Goal
Introduce staff roles and permissions so internal routes and navigation can be gated for `super_admin`, `admin`, and `cashier`.

## Scope
- Extend user model to support staff access metadata.
- Add role and permission tables with seed data.
- Attach one role per user for v1 and expose derived permissions.
- Protect internal routes with custom middleware.
- Hide navigation items when user lacks access.
- Disable open public registration for now.

## Files to Modify
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/*create_roles*`
- `database/migrations/*create_permissions*`
- `database/migrations/*create_role_permissions*`
- `database/migrations/*add_role_to_users*`
- `database/seeders/*`
- `app/Models/User.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `bootstrap/app.php`
- `routes/web.php`
- `routes/auth.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/Layouts/AuthenticatedLayout.jsx`
- `resources/js/Pages/Welcome.jsx`

## Interface / Behavior
- User belongs to one role in v1.
- Permissions are derived from role-permission mapping.
- Route middleware aliases:
  - `staff`
  - `permission:{code}`
- Route policy for v1:
  - `/dashboard` requires `access-admin`
  - `/pos` requires `access-pos`
- Registration route disabled for guests.

## Seed Data
- Roles:
  - `super_admin`
  - `admin`
  - `cashier`
- Permissions:
  - `access-admin`
  - `access-pos`
  - `manage-users`
  - `manage-settings`
  - `manage-master-data`
  - `manage-bookings`
  - `manage-payments`
- Role mapping:
  - `super_admin`: all above
  - `admin`: `access-admin`, `manage-settings`, `manage-master-data`, `manage-bookings`, `manage-payments`
  - `cashier`: `access-pos`, `manage-bookings`, `manage-payments`

## Validation Plan
- `php artisan migrate:fresh --seed`
- `php artisan test`
- `npm run build`
- `php artisan route:list`

## Risks / Notes
- Existing Breeze registration must be constrained so public users cannot self-provision staff accounts.
- Permission checks should stay simple; no many-role-per-user logic in v1.
- Login gate should reject inactive or non-staff accounts.

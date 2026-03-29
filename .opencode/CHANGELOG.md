# Changelog

## 2026-03-29
- Bootstrapped Laravel 12 application.
- Installed Laravel Breeze with Inertia React scaffold.
- Built frontend assets successfully.
- Noted environment warning: Node.js `20.10.0` is below Vite recommended `20.19+`, but production build completed.
- Replaced default Breeze landing page with Land Station public foundation shell.
- Added branded guest and authenticated layouts for admin and POS flows.
- Added initial POS foundation page and route.
- Added role and permission foundations for `super_admin`, `admin`, and `cashier`.
- Added staff and permission middleware with permission-aware route protection.
- Disabled public registration and refined staff login behavior.

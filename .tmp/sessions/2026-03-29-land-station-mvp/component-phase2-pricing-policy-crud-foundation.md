# Component: Phase 2 Pricing and Booking Policy CRUD Foundation

## Goal
Introduce the next management CRUD layer so admin users can create and update pricing rules plus booking policies on top of the existing service catalog foundation.

## Scope
- Keep current auth, route names, and permission model intact.
- Reuse `manage-master-data`.
- Add create/update flows for:
  - `service_pricing_rules`
  - `service_booking_policies`
- Extend the existing service catalog management page.
- No delete flow yet.

## Files to Create / Modify

### Create
- `app/Http/Controllers/Admin/ServicePricingRuleController.php`
- `app/Http/Controllers/Admin/ServiceBookingPolicyController.php`
- `app/Http/Requests/Admin/StoreServicePricingRuleRequest.php`
- `app/Http/Requests/Admin/UpdateServicePricingRuleRequest.php`
- `app/Http/Requests/Admin/StoreServiceBookingPolicyRequest.php`
- `app/Http/Requests/Admin/UpdateServiceBookingPolicyRequest.php`

### Modify
- `routes/web.php`
- `app/Http/Controllers/Admin/ServiceController.php`
- `resources/js/Pages/Admin/Services/Index.jsx`
- `tests/Feature/Admin/ServiceCatalogTest.php`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Add management-protected create/update routes for pricing rules and booking policies.
- Extend current Inertia service catalog screen with read/write sections for both models.

## Validation Plan
- Write failing feature tests for props, create/update success, and validation failures.
- Verify red.
- Implement thin controllers + FormRequests + UI sections.
- Verify green.
- Re-run build and broader suite.

## Risks / Open Questions
- Pricing rule unit must belong to the same service.
- Booking policy is unique per service; create should fail on duplicate.
- Money fields must stay integer `*_rupiah`.
- Keep the slice narrow: no delete, no conflict-resolution logic, no availability engine.

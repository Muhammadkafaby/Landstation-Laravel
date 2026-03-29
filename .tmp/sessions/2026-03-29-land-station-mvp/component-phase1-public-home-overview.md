# Component: Phase 1 Public Homepage Overview

## Goal
Replace the homepage’s static service/module overview with a DB-backed summary so the public landing page reflects the current flexible service foundation.

## Scope
- Keep `/` route and `home` name unchanged.
- Keep `Public/Home` page path unchanged.
- Load guest-safe overview props from seeded service catalog data.
- Show only read-only summary and featured service/category signals.
- No booking actions or customer forms yet.

## Files to Create / Modify

### Create
- `tests/Feature/Public/PublicHomeOverviewTest.php`

### Modify
- `app/Http/Controllers/Public/HomeController.php`
- `resources/js/Pages/Public/Home.jsx`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Data and Route Impact
- No schema changes.
- Public homepage becomes data-backed.
- Route contract stays stable.

## Validation Plan
- Write failing feature tests for overview props and one seeded category/service signal.
- Verify red.
- Implement thin controller prop shaping + presentational homepage update.
- Verify green.
- Re-run build + selected public/admin/database suite.

## Risks / Open Questions
- Keep homepage lighter than `/services`; avoid duplicating full catalog detail.
- Public copy should not imply live booking yet.
- Do not expose raw IDs or internal status details.

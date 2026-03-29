# Component: Foundation Scaffold and App Shells

## Goal
Prepare the application shell for Land Station with clear `Public`, `Admin`, and `Pos` entry experience on top of the Breeze React scaffold.

## Scope
- Keep Breeze auth working.
- Replace generic welcome/dashboard language with Land Station foundation copy.
- Create page shells/layout direction for `Public`, `Admin`, and `Pos`.
- Do not implement business modules yet.

## Files Expected
- Modify route entry files for public/admin landing.
- Modify React page/layout files produced by Breeze.
- Add shared navigation and shell components if needed.

## Validation
- `npm run build`
- `php artisan route:list`

## Risks
- Node version warning is present; build currently succeeds.
- Database-backed auth pages are scaffolded but not yet migrated locally.

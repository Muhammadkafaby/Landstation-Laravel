# Component: Phase 8 Node Tooling Hardening and Production Guidance

## Goal
Codify the frontend runtime expectation and production-readiness guidance so the repository stops depending on implicit local knowledge for Node/Vite compatibility and frontend build verification.

## Scope
- Add Node engine constraint to `package.json` aligned with Vite 7.
- Add a lightweight Node version file for local tooling.
- Update CI workflow to install matching Node and verify frontend build.
- Replace generic README guidance with repo-specific setup and verification notes.
- No schema changes.
- No business logic changes.

## Files to Modify
- `package.json`
- `README.md`
- `.github/workflows/tests.yml`
- `.opencode/CHANGELOG.md`
- `.tmp/sessions/2026-03-29-land-station-mvp/master-plan.md`

## Files to Create
- `.nvmrc`

## Validation Plan
- Inspect current scripts/README/workflow first.
- Apply minimal metadata/docs/CI changes.
- Verify `npm run build` still passes.
- Update changelog and master plan.

## Risks / Open Questions
- Keep engine constraint descriptive, not disruptive to project behavior.
- Avoid changing PHP workflow logic beyond adding Node/frontend verification.
- Keep README concise and repo-specific.

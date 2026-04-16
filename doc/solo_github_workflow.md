# Solo GitHub Workflow (Minimal)

## Branches
- `dev`: daily development
- `main`: stable branch for integration/testing

## Merge rule
- Merge to `main` via PR from `dev`
- Required check: `CI / build-test`
- No mandatory reviewer

## CI flow
- CI: `.github/workflows/ci.yml`
  - Trigger: push `dev|main`, PR vào `main`
  - Run: install deps, `migrate:fresh --seed`, build frontend, stable test pack

## Milestones
- `v1-phase-1`
- `v1-phase-2`
- `v1-phase-3`
- `v1-phase-4`

## PR checklist
- Scope is clear
- Migration impact documented
- Rollback note available

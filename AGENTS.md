## Learned User Preferences
- Prefers a minimal solo-developer GitHub workflow focused on development and testing, without automated deploy steps in shared CI.
- Prefers Filament-first, modular MVP implementation for this project before expanding architecture.
- Prefers backward-compatible schema changes (additive migrations) when implementing large roadmap slices rather than destructive cutovers.
- Prefers warn-first operational gates with auditable overrides before promoting hard blocks on critical constraints.

## Learned Workspace Facts
- The repository is an active git workspace at `web-vsen` with ongoing product/database and ops-related implementation work.
- Parent agent transcripts for this workspace are persisted under the Cursor project transcript store and should be processed incrementally.
- Current Ops direction includes standardizing public procurement tender documents into immutable Tender Snapshot data before mapping to execution runtime entities.
- Product delivery follows a phased Business OS roadmap (`doc/implementation_roadmap.md`, `model/*`): foundation, core demand (`Order`), supply/inventory, delivery/cash, governance.
- Ops runtime `Contract`/`ContractItem` are treated as execution projections tied to demand snapshots and `Order`, not as a parallel legal-contract source versus Muasamcong.
- The default GitHub Actions workflow does not run `tests/Feature/Ops`; Ops coverage requires explicit test runs or extending the workflow.
- `model/states.yaml` canonical labels may still diverge from runtime `Order` state strings until reconciled.
- Doc/model alignment is checked with `scripts/audit_doc_model_consistency.py` (run via `python3`).

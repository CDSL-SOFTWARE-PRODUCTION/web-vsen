## Learned User Preferences
- Prefers a minimal solo-developer GitHub workflow focused on development and testing, without automated deploy steps in shared CI.
- Prefers complete Vietnamese (with diacritics) for Ops Filament screens via project i18n keys; avoids hardcoded user-facing strings and mixed partial English/Vietnamese on the same screen.
- Prefers Filament-first, modular MVP implementation for this project before expanding architecture.
- Prefers incremental delivery and local optimization over global refactors or attempting to hold the entire Business OS in working memory at once; ships one narrow vertical slice at a time to limit overbloat.
- Prefers backward-compatible schema changes (additive migrations) when implementing large roadmap slices rather than destructive cutovers.
- Prefers warn-first operational gates with auditable overrides before promoting hard blocks on critical constraints.
- De-prioritizes MISA, bank/VA webhooks, and other external integrations until internal domain logic (states, constraints, gates, ledger) is consistent; keeps ports/null adapters without production vendor work as the default focus.
- Prefers long instructional copy on dense Ops forms to live behind a single hint icon or tooltip rather than multiple always-visible helper paragraphs under fields.
- Expects the Ops dashboard to read like a compact SaaS-style overview (grouped KPI strips, coherent hierarchy) when changing layout, rather than many separate Filament stat-widget stacks that repeat headings.
- For master-data–only work in Ops, prefers a reduced shell (dedicated home and scoped sidebar for role `DuLieuNen`) over the default operational KPI dashboard and full resource tree.

## Learned Workspace Facts
- Parent agent transcripts for this workspace are persisted under the Cursor project transcript store and should be processed incrementally.
- TBMT-shaped demo payloads for Ops are stored as JSON under `database/fixtures/tender_snapshots/` and can be loaded via optional Laravel seeders; flags such as `OPS_SEED_DEMO_TENDER` and `OPS_SEED_HUE_MILK_FULL` (see `.env.example`) gate demo tender seeding—there is no in-app Muasamcong scraper/API for this workflow.
- Current Ops direction includes standardizing public procurement tender documents into immutable Tender Snapshot data before mapping to execution runtime entities.
- Product delivery follows a phased Business OS roadmap (`doc/guide.md`, `model/*`): foundation, core demand (`Order`), supply/inventory, delivery/cash, governance.
- Ops runtime `Contract`/`ContractItem` are treated as execution projections tied to demand snapshots and `Order`, not as a parallel legal-contract source versus Muasamcong.
- The default GitHub Actions workflow does not run `tests/Feature/Ops`; Ops coverage requires explicit test runs or extending the workflow.
- `model/states.yaml` canonical labels may still diverge from runtime `Order` state strings until reconciled.
- Doc/model alignment is checked with `scripts/audit_doc_model_consistency.py` (run via `python3`).
- Medical device (TBYT) dossier classes A and B are modeled as permanent dossier validity; classes C and D use a five-year registration cycle for canonical product documents (`MedicalDeviceDossierClass` and related Filament labels).
- Ops role `DuLieuNen` uses `MasterDataHome` as panel home, hides operational KPI widgets on `Dashboard`, and limits the Filament sidebar to whitelisted master-data resources (`OpsResource` / `FilamentAccess::isMasterDataSteward()`); `MedicalDeviceDeclaration` links many `CanonicalProduct` rows via `medical_device_declaration_id`, with bulk assign on the SKU list and create/associate/dissociate on the declaration relation manager (run `php artisan migrate` if `medical_device_declarations` is missing).
- `canonical_products.image_urls` stores multiple HTTPS image URLs as JSON (Filament repeater); legacy single `image_url` is migrated into that shape where applicable.
- The Ops Filament dashboard registers merged KPI strip widgets (`OpsExecutionAndRiskKpiWidget`, `OpsDemandAndSupplyKpiWidget`, `OpsMilestonesAndLiquidityKpiWidget`, `OpsDebtAndLedgerKpiWidget`) instead of many single-purpose stats widgets for the same metric groups.

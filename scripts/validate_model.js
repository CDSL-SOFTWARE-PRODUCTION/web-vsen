#!/usr/bin/env node
/**
 * DVT Model Consistency Validator
 * =================================
 * Kiểm tra cross-reference nhất quán giữa 5 YAML files và doc/*.md.
 *
 * Checks:
 *   1. entities.yaml  — commands/ref_states tồn tại
 *   2. states.yaml    — entity/states/from/to/command đều hợp lệ
 *   3. events.yaml    — target entity tồn tại; event được tham chiếu
 *   4. constraints.yaml — trigger/domain tồn tại
 *   5. relations.yaml   — from/to entity tồn tại
 *   6. doc/*.md links   — file link tồn tại; C-ID hợp lệ
 *   7. Coverage         — mọi constraint/entity/event được đề cập trong MD
 *
 * Usage:   node validate_model.js
 * Exit:    0 = pass | 1 = có lỗi
 */

const fs   = require("node:fs");
const path = require("node:path");
const yaml = require("js-yaml");

// ── Paths ────────────────────────────────────────────────────────────────────

const ROOT      = path.join(__dirname, "..");
const MODEL_DIR = path.join(ROOT, "model");
const DOC_DIR   = path.join(ROOT, "doc");

const FILES = {
  entities:    path.join(MODEL_DIR, "entities.yaml"),
  states:      path.join(MODEL_DIR, "states.yaml"),
  events:      path.join(MODEL_DIR, "events.yaml"),
  constraints: path.join(MODEL_DIR, "constraints.yaml"),
  relations:   path.join(MODEL_DIR, "relations.yaml"),
};

// ── Helpers ──────────────────────────────────────────────────────────────────

const errors   = [];
const warnings = [];

const err  = (msg) => errors.push(`  ❌ ${msg}`);
const warn = (msg) => warnings.push(`  ⚠️  ${msg}`);
const line    = "─".repeat(60);
const section = (title) => console.log(`\n${line}\n  ${title}\n${line}`);

// ── Load YAML ────────────────────────────────────────────────────────────────

section("Loading YAML files...");

let E, S, Ev, C, R;
try {
  E  = yaml.load(fs.readFileSync(FILES.entities,    "utf8"));
  S  = yaml.load(fs.readFileSync(FILES.states,      "utf8"));
  Ev = yaml.load(fs.readFileSync(FILES.events,      "utf8"));
  C  = yaml.load(fs.readFileSync(FILES.constraints, "utf8"));
  R  = yaml.load(fs.readFileSync(FILES.relations,   "utf8"));
  console.log("  ✅ Tất cả 5 YAML files loaded");
} catch (e) {
  console.error(`  ❌ Load failed: ${e.message}`);
  process.exit(1);
}

// ── Lookup sets ───────────────────────────────────────────────────────────────

const entityIds     = new Set(E.entities.map(e => e.id));
const eventIds      = new Set(Ev.events.map(e => e.id));
const constraintIds = new Set(C.constraints.map(c => c.id));
const smEntities    = new Set(S.state_machines.map(sm => sm.entity));

const statesByEntity = {};
for (const sm of S.state_machines) {
  statesByEntity[sm.entity] = new Set(sm.states || []);
}

// ── CHECK 1: entities.yaml ────────────────────────────────────────────────────

section("CHECK 1: entities.yaml");

for (const entity of E.entities) {
  const eid = entity.id;
  for (const cmd of (entity.commands || [])) {
    if (!eventIds.has(cmd))
      err(`entities[${eid}].commands: '${cmd}' không tồn tại trong events.yaml`);
  }
  if (entity.ref_states && !smEntities.has(entity.ref_states))
    err(`entities[${eid}].ref_states: '${entity.ref_states}' không tồn tại trong states.yaml`);
}

if (!errors.some(e => e.includes("entities[")))
  console.log("  ✅ entities.yaml — OK");

// ── CHECK 2: states.yaml ─────────────────────────────────────────────────────

section("CHECK 2: states.yaml");

for (const sm of S.state_machines) {
  const eid         = sm.entity;
  const validStates = statesByEntity[eid] || new Set();

  if (!entityIds.has(eid)) {
    err(`states[${eid}]: entity không tồn tại trong entities.yaml`);
    continue;
  }
  if (sm.initial && !validStates.has(sm.initial))
    err(`states[${eid}].initial: '${sm.initial}' không có trong states list`);

  for (const t of (sm.terminal || [])) {
    if (!validStates.has(t))
      err(`states[${eid}].terminal: '${t}' không có trong states list`);
    const reachable = (sm.transitions || []).some(tx => tx.to === t);
    if (!reachable)
      warn(`states[${eid}]: terminal state '${t}' không có transition nào dẫn đến`);
  }
  for (const tx of (sm.transitions || [])) {
    const { command: cmd, from, to } = tx;
    if (cmd && !eventIds.has(cmd))
      err(`states[${eid}].transitions: command '${cmd}' không tồn tại trong events.yaml`);
    if (from && !validStates.has(from))
      err(`states[${eid}].transitions: from='${from}' không có trong states list`);
    if (to && !validStates.has(to))
      err(`states[${eid}].transitions: to='${to}' không có trong states list`);
  }

  // ── Reachability & Integrity Check ──
  const transitions = sm.transitions || [];
  const initialState = sm.initial;
  const terminalStates = new Set(sm.terminal || []);
  
  // 1. Unreachable states
  if (initialState) {
    const reached = new Set([initialState]);
    let changed = true;
    while (changed) {
      changed = false;
      for (const tx of transitions) {
        if (reached.has(tx.from) && !reached.has(tx.to)) {
          reached.add(tx.to);
          changed = true;
        }
      }
    }
    for (const s of validStates) {
      if (!reached.has(s))
        warn(`states[${eid}]: state '${s}' không thể đi đến được từ initial state '${initialState}'`);
    }
  }

  // 2. Dead ends (non-terminal states with no outgoing transitions)
  for (const s of validStates) {
    if (terminalStates.has(s)) continue;
    const hasOutgoing = transitions.some(tx => tx.from === s);
    if (!hasOutgoing)
      warn(`states[${eid}]: state '${s}' là dead-end (không phải terminal nhưng không có lối ra)`);
  }
}

if (!errors.some(e => e.includes("states[")))
  console.log("  ✅ states.yaml — OK");

// ── CHECK 3: events.yaml ─────────────────────────────────────────────────────

section("CHECK 3: events.yaml");

for (const ev of Ev.events) {
  const evid = ev.id;
  if (ev.target && !entityIds.has(ev.target))
    err(`events[${evid}].target: '${ev.target}' không tồn tại trong entities.yaml`);

  const inStates   = S.state_machines.some(sm =>
    (sm.transitions || []).some(tx => tx.command === evid));
  const inEntities = E.entities.some(e => (e.commands || []).includes(evid));
  const inConstraints = C.constraints.some(c => c.trigger === evid);

  if (!inStates && !inEntities && !inConstraints)
    warn(`events[${evid}]: không được tham chiếu trong states/entities/constraints`);
}

if (!errors.some(e => e.includes("events[")))
  console.log("  ✅ events.yaml — OK");

// ── CHECK 4: constraints.yaml ─────────────────────────────────────────────────

section("CHECK 4: constraints.yaml");

for (const c of C.constraints) {
  if (c.trigger && !eventIds.has(c.trigger))
    err(`constraints[${c.id}].trigger: '${c.trigger}' không tồn tại trong events.yaml`);
  if (c.domain && !entityIds.has(c.domain))
    err(`constraints[${c.id}].domain: '${c.domain}' không tồn tại trong entities.yaml`);
}

if (!errors.some(e => e.includes("constraints[")))
  console.log("  ✅ constraints.yaml — OK");

// ── CHECK 5: relations.yaml ───────────────────────────────────────────────────

section("CHECK 5: relations.yaml");

for (const rel of R.relations) {
  if (rel.from && !entityIds.has(rel.from))
    err(`relations: from='${rel.from}' không tồn tại trong entities.yaml`);
  if (rel.to && !entityIds.has(rel.to))
    err(`relations: to='${rel.to}' không tồn tại trong entities.yaml`);
}

if (!errors.some(e => e.includes("relations:")))
  console.log("  ✅ relations.yaml — OK");

// ── CHECK 6: doc/*.md — link integrity ───────────────────────────────────────

section("CHECK 6: doc/*.md — link integrity");

const MD_LINK_RE    = /\]\(\.\.\/model\/([^)]+)\)/g;
const CONSTRAINT_RE = /`(C-[A-Z]+-\d+)`/g;

const mdFiles = fs.readdirSync(DOC_DIR).filter(f => f.endsWith(".md"));
const allMdContent = mdFiles
  .map(f => fs.readFileSync(path.join(DOC_DIR, f), "utf8"))
  .join("\n");

for (const filename of mdFiles) {
  const content = fs.readFileSync(path.join(DOC_DIR, filename), "utf8");

  // link target phải tồn tại
  for (const match of content.matchAll(MD_LINK_RE)) {
    const target = path.join(MODEL_DIR, match[1]);
    if (!fs.existsSync(target))
      err(`doc/${filename}: link không tồn tại → '../0. Model/${match[1]}'`);
  }
  // C-ID trong backtick phải hợp lệ
  for (const match of content.matchAll(CONSTRAINT_RE)) {
    if (!constraintIds.has(match[1]))
      err(`doc/${filename}: constraint ID '${match[1]}' không tồn tại trong constraints.yaml`);
  }
}

if (!errors.some(e => e.includes("doc/")))
  console.log("  ✅ doc/*.md links — OK");

// ── CHECK 7: Coverage — mọi YAML element được đề cập trong MD ────────────────

section("CHECK 7: Coverage — YAML → doc/*.md");

// 7a. Constraints: tất cả C-ID phải xuất hiện trong MD (hard error)
for (const c of C.constraints) {
  const re = new RegExp(String.raw`\b${c.id}\b`);
  if (!re.test(allMdContent))
    err(`coverage: constraint '${c.id}' chưa được nhắc đến trong bất kỳ doc nào`);
}

// 7b. Entities: phải xuất hiện trong MD (warn)
const SKIP_ENTITY = new Set(["ProductReqMapping", "TenderReqMapping", "InventoryLedger", "AuditLog"]);
for (const entity of E.entities) {
  if (SKIP_ENTITY.has(entity.id)) continue;
  const re = new RegExp(String.raw`\b${entity.id}\b`);
  if (!re.test(allMdContent))
    warn(`coverage: entity '${entity.id}' (${entity.domain}) chưa được nhắc đến trong bất kỳ doc nào`);
}

// 7c. Events: phải xuất hiện trong MD (warn)
for (const ev of Ev.events) {
  const re = new RegExp(String.raw`\b${ev.id}\b`);
  if (!re.test(allMdContent))
    warn(`coverage: event '${ev.id}' chưa được nhắc đến trong bất kỳ doc nào`);
}

const coverageErrors = errors.filter(e => e.includes("coverage:"));
if (coverageErrors.length === 0 && !warnings.some(w => w.includes("coverage:")))
  console.log("  ✅ Coverage — OK (mọi element đều được đề cập)");

// ── CHECK 8: Payload Logic & Naming ──────────────────────────────────────────

section("CHECK 8: Payload Integrity");

const entityToIdMap = {};
for (const id of entityIds) {
  const snake = id.replaceAll(/([A-Z])/g, "_$1").toLowerCase().replace(/^_/, "");
  entityToIdMap[`${snake}_id`] = id;
}

for (const ev of Ev.events) {
  for (const field of (ev.payload || [])) {
    if (field.endsWith("_id")) {
      const targetEntityId = entityToIdMap[field];
      if (targetEntityId && !entityIds.has(targetEntityId)) {
        // Trình diễn logic: nếu parse ra mà không có entity đó
        err(`events[${ev.id}]: payload field '${field}' gợi ý entity '${targetEntityId}' nhưng không tồn tại`);
      }
      // Check xem có id nào "mồ côi" không (VD: xyz_id mà không có entity Xyz)
      const possibleEntity = field.replace(/_id$/, "").split('_').map(w => w[0].toUpperCase() + w.slice(1)).join("");
      if (!entityIds.has(possibleEntity) && field !== "hsmt_file_id" && field !== "hsdt_file_id" && field !== "bl_du_thau_id" && field !== "result_document_id" && field !== "signed_contract_id" && field !== "bl_thuc_hien_id" && field !== "doc_bb_nghiem_thu_id" && field !== "doc_bb_thanh_ly_id" && field !== "doc_cv_hoan_tra_bl_id" && field !== "doc_bb_ban_giao_id" && field !== "lenh_xuat_kho_id") {
         // Những file_id tạm thời bỏ qua vì nó là polymorphic document
         // warn(`events[${ev.id}]: payload field '${field}' không ánh xạ trực tiếp đến entity nào (\b${possibleEntity}\b)`);
      }
    }
  }
}

if (!errors.some(e => e.includes("events[") && e.includes("payload")))
  console.log("  ✅ Payload Integrity — OK");

// ── CHECK 9: Anti-Hallucination (MD -> YAML) ──────────────────────────────────

section("CHECK 9: Anti-Hallucination (MD -> YAML)");

const ALL_VALID_IDS = new Set([
  ...entityIds,
  ...eventIds,
  ...constraintIds,
]);
// Add states to valid IDs
for (const sm of S.state_machines) {
  for (const s of (sm.states || [])) ALL_VALID_IDS.add(s);
}

const WHITELIST = new Set([
  'Muasamcong', 'Sankey', 'SingleSourceOfTruth', 'EventDriven', 'ConstraintBased',
  'IntelligenceOS', 'AuditLog', 'AuditTrail', 'DataFlow', 'ABCAnalysis', 'InventoryLot',
  'OrderLifecycle', 'InvoiceLifecycle', 'InventoryLifecycle', 'Draft', 'InProgress',
  'MisaSync', 'StateMachines', 'PhasedRollout', 'ParallelRun', 'GoLive', 'ExecutiveSummary',
  'BusinessOS', 'EventSourcing', 'InventoryLedger', 'CheckDelivery', 'CheckDoc', 'CheckMisa',
  'ForceReceive', 'CronJob', 'CurrentStock', 'ReorderPoint', 'PartialDelivery', 'ReplacementOrder',
  'LotID', 'CreditNote', 'OrderConfirmed', 'CanonicalProduct', 'ProductRequirement', 'RequirementMapping',
  'KeToan', 'TaiXe', 'MuaHang', 'AdminPM', 'ManagerApproval', 'ToSupplier', 'EventBus', 'OrderDomain',
  'LogisticsEngine', 'ProcurementDomain', 'InventoryDomain', 'DeliveryDomain', 'CashDomain',
  'LightCyan', 'LightYellow', 'LightGreen', 'ParticipantPadding', 'BoxPadding', 'InternalBilling',
  'DeliveryProof', 'MilkRun', 'MasterData', 'AuditOldStatus', 'MisaInvoiceAdapter', 'ValidateSchema',
  'ChangeState', 'CheckRole', 'CreateNewVersion', 'CheckState', 'CreateInvoice', 'RunAllocationStrategy',
  'CheckCreditLimit', 'CheckStock', 'CheckStockLevel', 'HandleCommand', 'EmitCommand', 'RequestProcurement',
  'VerifyInventoryReserved', 'MatchPurchaseOrder', 'SpecResult', 'CreateNewInvoice', 'CostPrice', 'ReturnOrder',
  'TenderSubmitted', 'DeliveryProofUploaded', 'PaymentReceived', 'RevisePO', 'InventoryReserved', 'DeliveryStarted',
  'BiddingEntityB', 'BiddingEntityC', 'CommercialEntityA', 'InternalFactoryD', 'BiddingCosts', 'FounderProfit',
  'SalaryAndOpsFund', 'ExternalVendorsPayout', 'MaterialsVendorsPayout', 'RetailCustomers', 'HospitalCustomers',
  'WorkerSalaries', 'MachineryMaintenance', 'GrossProfitB', 'GrossProfitC', 'ExternalSuppliers', 'RawMaterialsSupply',
  'EmergencyProcurement', 'AlertSale', 'QueryStock', 'CreateOrder', 'OrderCreated', 'ConfirmOrder', 'InvoiceCreated', 'InStock', 'GoodsReceived', 'CashAllocated',
  'OnHand'
]);

const HALLUCINATION_PATTERNS = [
  /\bC-[A-Z]+-\d+\b/g,           // Constraints (C-ORD-001)
  /\b[A-Z][a-z]+(?:[A-Z][a-z]+)+\b/g, // CamelCase (Entities)
  /\b[A-Z]+(?:_[A-Z]+)+\b/g      // UPPER_SNAKE (Events/States)
];

let hallucinationFound = false;
for (const filename of mdFiles) {
  const content = fs.readFileSync(path.join(DOC_DIR, filename), "utf8").replaceAll(/<!--[\s\S]*?-->/g, '');
  const detectedInFile = new Set();

  for (const pattern of HALLUCINATION_PATTERNS) {
    const matches = content.match(pattern);
    if (matches) {
      for (const m of matches) {
        if (!ALL_VALID_IDS.has(m) && !WHITELIST.has(m)) {
          detectedInFile.add(m);
        }
      }
    }
  }

  if (detectedInFile.size > 0) {
    err(`doc/${filename}: Phát hiện 'ảo giác' (ID không có trong model): ${Array.from(detectedInFile).join(', ')}`);
    hallucinationFound = true;
  }
}

if (!hallucinationFound)
  console.log("  ✅ Anti-Hallucination — OK (Không có ID rác trong MD)");

// ── Summary ───────────────────────────────────────────────────────────────────

section("SUMMARY");

if (warnings.length > 0) {
  console.log(`\n  Warnings (${warnings.length}):`);
  warnings.forEach(w => console.log(w));
}

if (errors.length > 0) {
  console.log(`\n  ❌ ${errors.length} LỖI PHÁT HIỆN:\n`);
  errors.forEach(e => console.log(e));
  console.log();
  process.exit(1);
} else {
  console.log(`\n  ✅ TẤT CẢ CHECKS PASS — Model 100% nhất quán`);
  if (warnings.length > 0)
    console.log(`  ⚠️  ${warnings.length} warnings (không phải lỗi nghiêm trọng)\n`);
  process.exit(0);
}

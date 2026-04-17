# Implementation Roadmap (Business OS Build Path)

Mục tiêu của file này: biến `model/*` + `doc/*` thành backlog có thứ tự để build được **toàn bộ Business OS**, không chỉ Ops runtime.

Nguyên tắc nền (ref: `doc/system_architecture.md`)
- **Model-first**: sửa `model/*` trước, app theo sau.
- **Event + Constraint = Physics**: không đi state bằng UI tắt.
- **Document là Proof**: điều kiện mở cổng state.
- **Human-in-the-loop**: máy gợi ý, người confirm.
- **Backward-compatible rollout**: ưu tiên add, không phá flow đang chạy.

### Đối chiếu với `doc/system_architecture.md`

- **`system_architecture.md`** mô tả **toàn bộ** Business OS (Knowledge, từng layer, Planning/Logistics, EventBus, MISA, webhook ngân hàng, …). Đó là **North Star / blueprint**, không phải checklist “đã xong” cho mỗi PR.
- **File roadmap này** là **đường build có phase**: “đủ” được đo theo **North Star (5 điều kiện)** và **definition of done từng phase** (A → E), không yêu cầu khớp 100% blueprint trước khi Phase D/E ổn định.
- Khi cần hỏi “đã đủ chưa?”, ưu tiên: (1) phase hiện tại trong bảng dưới, (2) khối **North Star**, (3) tích hợp ngoài / Intelligence nằm trong [`doc/backlog/post_mvp_integrations.md`](backlog/post_mvp_integrations.md) sau MVP Ops.

### Ưu tiên build hiện tại (logic doanh nghiệp trước)

- **Trọng tâm:** `Order` / constraint / gate / ledger / projection **nhất quán** với `model/*` và test tích hợp — đây là nền bắt buộc trước khi mở rộng UX hay Intelligence.
- **Hoãn chủ động:** tích hợp **MISA**, **webhook ngân hàng / VA**, và các adapter bên ngoài khác — giữ **port + null adapter** trong code để dev không phụ thuộc vendor; không ưu tiên triển khai production cho các kênh này cho đến khi nội bộ đã “chặt”. Chi tiết hoãn xem [`doc/backlog/post_mvp_integrations.md`](backlog/post_mvp_integrations.md).

---

## Trạng thái hiện tại

Đã có nền Ops runtime và slice hậu trúng thầu:
- `TenderSnapshot` + Lock + Generate plan
- `Contract`, `ContractItem`, `Document`, `PaymentMilestone`, `ExecutionIssue`, `CashPlanEvent`
- Risk cache/dashboard, gate warn-first, audit trail nền

Khoảng trống còn lại (sau Phase A–C trong code):
- `model/states.yaml` (tên state canonical) có thể lệch chuỗi state runtime (`Order` trong DB) — cần đồng bộ một lần (Phase E / hygiene).
- CI mặc định: chạy `tests/Feature/Ops` (xem workflow) để khóa regression; chạy `scripts/audit_doc_model_consistency.py` trên PR.
- Reserve inventory: TTL/cron release (tùy chọn, sau Delivery/Cash ổn định).

---

## North Star: Full Business OS

Done khi đạt đủ 5 điều kiện:
1. `Order` là aggregate root thật sự theo `model/states.yaml`.
2. Mọi domain chính chạy được theo luồng dọc: Demand -> Supply -> Inventory -> Delivery -> Cash.
3. Constraint IDs trong `model/constraints.yaml` có enforcement rõ (hard hoặc warn+audit).
4. ERD trong `doc/system_architecture.md` và `model/entities.yaml` luôn đồng bộ (audit script pass).
5. Mỗi domain có test tích hợp tối thiểu cho happy path + failure gates.

---

## Bảng đối chiếu blueprint vs codebase

Hai tài liệu [`system_architecture.md`](system_architecture.md) và [`business_workflows.md`](business_workflows.md) là **North Star / UX target** — không phải trạng thái “đã ship 100%”. Bảng dưới ước lượng **mức đã gắn với code** tại thời điểm cập nhật roadmap.

**Chú thích cột Trạng thái**

| Ký hiệu | Ý nghĩa |
| --- | --- |
| **Đủ** | Có phần lõi trong app (domain + persistence + thường có test Ops) — đủ vận hành MVP/Filament. |
| **Một phần** | Có slice hoặc gate/warn; thiếu journey UI đầy đủ hoặc tính năng phụ trong doc. |
| **Chưa** | Chủ yếu còn trên paper / backlog (xem [`post_mvp_integrations.md`](backlog/post_mvp_integrations.md) nếu liên quan tích hợp ngoài). |

### `doc/system_architecture.md` — các khối kiến trúc

| Khối / mục (tham chiếu doc) | Trạng thái | Ghi chú ngắn |
| --- | --- | --- |
| §0 Knowledge (Canonical Product, Tender Intelligence) | **Một phần** | Bảng `canonical_products` + upsert; chưa LLM/vector/normalize file như doc. |
| §1 Demand & Contract (`Order`, snapshot, state) | **Đủ** | Snapshot lock, plan, transition command, map runtime ↔ canonical (`model/order_state_mapping.yaml`). |
| §2 Inventory (`InventoryLot`, reserve, priority…) | **Một phần** | Nhận kho, reserve + TTL, transfer, return; chưa “priority engine” đầy đủ như mô tả. |
| §3 Delivery (thực địa, proof, GPS…) | **Một phần** | `Delivery`, vehicle/route, gate pre-delivery; chưa app tài xế / GPS cứng `C-DEL-002`. |
| §4 Cashflow (Invoice, ledger, MISA, VA) | **Một phần** | Issue invoice, ledger, milestone, aging; **MISA/VA** hoãn — port + null adapter. |
| §4b Post-award (Contract runtime, milestone, issue, price, touchpoint) | **Đủ** / **Một phần** | Projection + `SalesTouchpoint`; cash plan / gap vốn có thể mỏng hơn doc. |
| §5 Engineering core (Command → Constraint → Audit) | **Đủ** | `GateEvaluator`, audit, policy; EventBus đầy đủ như doc là mục tiêu dài hạn. |
| Planning / Logistics / ROP (`ops:rop-scan`, ABC…) | **Một phần** | Scan ngưỡng + audit; chưa ABC/Product class đầy đủ trong model app. |
| Tích hợp ngoài (MISA, webhook ngân hàng) | **Chưa** (hoãn) | Theo [`post_mvp_integrations.md`](backlog/post_mvp_integrations.md). |

### `doc/business_workflows.md` — hành trình & ma trận

| Mục trong doc | Trạng thái | Ghi chú ngắn |
| --- | --- | --- |
| Ma trận quyền (Sale / MuaHang / Kho / KeToan / Admin) | **Một phần** | [`ops_matrices.md`](ops_matrices.md) + Filament + `LegalEntity` scope; chưa tách app riêng từng vai. |
| Hành trình E2E (4 section journey) | **Một phần** | Luồng dọc **Demand → … → Cash** có test tích hợp; chưa UI journey riêng từng bộ phận. |
| Sale: Catalog / Builder / Document Vault | **Chưa** | Thay bằng Order / Contract / Snapshot trong Ops. |
| Mua hàng: Request Inbox / Sourcing PO | **Một phần** | Supply order từ thiếu hàng + Filament; chưa inbox/workflow đầy đủ. |
| Kho: Inbound quét barcode / App POD | **Một phần** | Nhập nhận qua domain; chưa app mobile POD như doc. |
| Kế toán: Billing đỏ / Payable list / Credit note đầy đủ | **Một phần** | Xuất HĐ + milestone + aging; payable/credit note mở rộng theo nhu cầu. |
| Founder: Sankey / Inter-company transfer | **Chưa** | Dashboard tiền mặt như mô tả chưa có. |
| Bảng edge cases + `C-*` | **Một phần** | Chi tiết enforce: [`ops_matrices.md`](ops_matrices.md) (mục 2); không phải mọi dòng edge case đã có code. |

**Kết luận ngắn:** Blueprint và journey **đã định hướng** và **đã khớp một phần lớn** với Phase A–E trong repo; **chưa** đồng nghĩa mọi màn hình và tích hợp trong hai file đã sẵn sàng vận hành production đầy đủ.

---

## Phase A — Stabilize Foundation (**đã triển khai trong code**)

### Mục tiêu
Chốt nền post-award để làm bệ phóng cho full OS.

### Scope (trạng thái)
- `TenderSnapshot` immutable + hash/version — **có** (`TenderSnapshot::lock`, hash payload).
- `GenerateExecutionPlan` — **có** (transaction, contract + items + milestone + audit).
- Gate pre-activate / pre-delivery / pre-payment warn-first + override audit — **có** (`GateEvaluator`, `GateOverrideService`).
- `AuditLog` — **có** (`AuditLogService`, Filament).

### Deliverables
- Resource UI Snapshot/Contract/Audit — **có** (Filament Ops).
- Feature tests luồng Snapshot → Runtime — **có** (`tests/Feature/Ops/ExecutionPlanFlowTest.php`); CI chạy gói Ops (workflow).

### Gap nhỏ
- Tiếp tục ưu tiên migration **additive** khi mở rộng schema.

---

## Phase B — Core Demand OS (`Order` as Aggregate Root) (**lõi đã có**)

### Mục tiêu
Đưa toàn bộ nghiệp vụ về trục `Order`/`OrderItem`.

### Map sang model
- Entities: `Order`, `OrderItem`, `Tender`, `TenderItem`, `SalesTouchpoint`, `PriceList`, `PriceListItem`
- States/events: `SubmitTender`, `AwardTender`, `ConfirmContract`, `StartExecution`, `ConfirmFulfillment`, `CloseContract`, `AbandonTender`
- Constraints: ưu tiên `C-ORD-*`, các ràng buộc credit/docs liên quan

### Build steps
1. Dựng state transition service cho `Order` (hard guard cho invalid transitions).
2. Nối `Tender/TenderItem` -> `Order/OrderItem` bằng command rõ input/output.
3. Tích hợp pricing (`PriceList/PriceListItem`) vào lúc chốt order lines.
4. Tích hợp `SalesTouchpoint` để không mất handover context.
5. Giảm dần nhập trực tiếp `Contract`; `Contract` chỉ còn projection.

### Acceptance
- Tạo/chuyển trạng thái order không bypass được constraints chính — **có** (`Order::transitionTo` + command services).
- Trace `Order` ↔ `Contract` — **có** (projection + tests).
- **Note:** tên state trong `model/states.yaml` (Draft, BidSubmitted, …) có thể khác chuỗi runtime (`AwardTender`, `ConfirmContract`, …) — reconcile trong Phase E.

---

## Phase C — Supply + Inventory OS (**đã có service + test**)

### Mục tiêu
Nối được nhu cầu mua hàng và tồn kho theo đúng physics.

### Map sang model
- Entities: `SupplyOrder`, `InventoryLot`, `InventoryReservation`, `InventoryLedger`, `StockTransfer`, `StockTransferLine`, `ReturnOrder`, `ReturnLineItem`
- Constraints: nhóm `C-SUP-*`, `C-INV-*`

### Build steps
1. `OrderItem` -> `SupplyOrder` (khi thiếu nguồn hàng).
2. Nhập kho tạo `InventoryLot` + append `InventoryLedger`.
3. Reserve theo `InventoryReservation` (lock/release rõ rule).
4. Điều chuyển kho với `StockTransfer`.
5. Quy trình return/re-stock/dispose.

### Acceptance
- Ledger IN/OUT/RESERVE — **có** (`InventoryLedger`, services).
- Reserve release / over-reserve — **có test**; TTL tự động — tùy chọn sau này.
- Thiếu hàng, transfer — **có test** trong `ExecutionPlanFlowTest`.

---

## Phase D — Delivery + Cash OS (**đã đạt acceptance tối thiểu trong code**)

### Mục tiêu
Hoàn tất vòng thực thi từ giao hàng đến hóa đơn/thu tiền.

### Map sang model
- Entities: `Delivery`, `DeliveryRoute`, `Vehicle`, `Invoice`, `Ledger`, `PaymentMilestone`, `Document`
- Constraints: nhóm `C-DEL-*`, `C-FIN-*`, `C-AR-*`, `C-EXE-*`

### Build steps
1. Dispatch delivery từ order/runtime projection.
2. Enforce proof docs trước milestone/payment readiness.
3. Issue invoice theo điều kiện giao hàng/chứng từ.
4. Ghi nhận ledger inflow/outflow/internal transfer.
5. Cảnh báo overdue/AR aging + cash gap.

### Acceptance (**đã có test + widget; mở rộng tiếp theo nhu cầu nghiệp vụ**)
- Không thể issue invoice khi thiếu điều kiện — **có** (`IssueInvoiceService` + `FulfillmentReadiness`; `tests/Feature/Ops/ExecutionPlanFlowTest.php`: reject khi thiếu giao/chứng từ; reject khi giao chưa `Delivered`).
- Payment readiness đi qua checklist rõ ràng — **có** (`GateEvaluator::evaluatePrePayment`, checklist milestone; cùng file test: gate pre-payment + milestone).
- Dashboard tài chính phản ánh đúng aging/gap — **có** (`AccountsReceivableAgingWidget`, `MilestoneAgingService`; test refresh `days_overdue_cached`).

### Gap tiếp (không chặn Phase D)
- `DeliveryRoute` / `Vehicle` đầy đủ như ERD: tùy slice logistics sau.
- Cash gap theo `CashPlanEvent`: bổ sung khi cần cảnh báo vốn chi tiết hơn widget hiện tại.

---

## Phase E — Governance, Intelligence, and Hardening (**đã đóng phần tối thiểu trong code**)

### Mục tiêu
Nâng từ MVP vận hành lên hệ thống bền vững.

### Scope
- Chuyển dần warn-first sang hard gate ở constraint critical (config + một số gate).
- Audit command đã có; replay schema — tùy roadmap sau.
- `scripts/audit_doc_model_consistency.py` trong CI.
- Runbook migration lớn — `doc/runbooks/`.
- Ma trận quyền + constraint — [`doc/ops_matrices.md`](ops_matrices.md).

### Trạng thái (đã có)
- **Gates:** `config/ops.php` — `confirm_fulfillment`, `invoice_payment_milestone` (`IssueInvoiceService` + audit khi `warn`).
- **CI:** `.github/workflows/ci.yml` — audit + `tests/Feature/Ops` (và gói stable khác).
- **Runbook:** `doc/runbooks/migration_large_changes.md` + checklist backfill / env gate.
- **Ma trận:** [`doc/ops_matrices.md`](ops_matrices.md), `App\Support\Ops\FilamentAccess` + policy / `canViewAny` trên resource Ops.

### Acceptance
- Enforcement mode rõ cho gate chọn lọc — **có** (env + `config/ops.php`).
- CI: doc/model audit + Ops tests — **có**.
- Runbook có mẫu cho migration/backfill — **có**.

### Gap tiếp (tùy chọn)
- Hard gate thêm cho các bước `GateEvaluator` khác (pre-delivery UI) nếu cần.
- Policy class / Laravel Policy thay cho `canViewAny` trên resource — khi số role phức tạp hơn.

---

## Dependency graph (thứ tự bắt buộc)

1. Phase A -> B (nền runtime + audit xong mới nâng aggregate root).
2. Phase B -> C (có demand chuẩn mới tính supply/inventory đúng).
3. Phase C -> D (delivery/cash phụ thuộc availability + reservation).
4. Phase D -> E (governance/hard gate sau khi luồng chính ổn định).

---

## Work mode đề xuất (để không loạn)

- Luôn triển khai theo **vertical slice**: UI + command/service + persistence + test.
- Mỗi PR chỉ 1 capability rõ (ví dụ: `Order state transition`, `Reserve inventory`, `Issue invoice gate`).
- Không mở domain mới nếu domain trước chưa có test integration pass.
- Mỗi phase đều phải có checklist “definition of done” trước khi qua phase tiếp theo.

---

## “Hôm nay làm gì?”

Thứ tự ưu tiên hiện tại (sau khi A–E đã có phần tối thiểu trong repo):
1. Đồng bộ `model/states.yaml` với runtime `Order` và ma trận ràng buộc `C-*` (enforce + test) — **logic nội bộ nhất quán trước**.
2. Hoàn thiện gate / ledger / luồng dọc theo North Star (reserve TTL, delivery proof, v.v.) khi còn gap.
3. Mở rộng Policy matrix Filament hoặc hard gate thêm nếu nghiệp vụ yêu cầu.
4. **Sau** khi (1)–(3) ổn: Intelligence / tích hợp ngoài — chỉ theo `doc/backlog/post_mvp_integrations.md` (MISA, VA, … không blocking).


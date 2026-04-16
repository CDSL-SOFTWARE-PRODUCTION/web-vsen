# Implementation Roadmap (Business OS Build Path)

Mục tiêu của file này: biến `model/*` + `doc/*` thành backlog có thứ tự để build được **toàn bộ Business OS**, không chỉ Ops runtime.

Nguyên tắc nền (ref: `doc/system_architecture.md`)
- **Model-first**: sửa `model/*` trước, app theo sau.
- **Event + Constraint = Physics**: không đi state bằng UI tắt.
- **Document là Proof**: điều kiện mở cổng state.
- **Human-in-the-loop**: máy gợi ý, người confirm.
- **Backward-compatible rollout**: ưu tiên add, không phá flow đang chạy.

---

## Trạng thái hiện tại

Đã có nền Ops runtime và slice hậu trúng thầu:
- `TenderSnapshot` + Lock + Generate plan
- `Contract`, `ContractItem`, `Document`, `PaymentMilestone`, `ExecutionIssue`, `CashPlanEvent`
- Risk cache/dashboard, gate warn-first, audit trail nền

Khoảng trống để hoàn thành Business OS:
- Core `Order` state machine chưa là trung tâm tuyệt đối.
- Demand/Supply/Inventory/Delivery/Finance chưa nối thành một luồng end-to-end.
- Pricing + Sales touchpoint chưa thành năng lực vận hành chính thức.

---

## North Star: Full Business OS

Done khi đạt đủ 5 điều kiện:
1. `Order` là aggregate root thật sự theo `model/states.yaml`.
2. Mọi domain chính chạy được theo luồng dọc: Demand -> Supply -> Inventory -> Delivery -> Cash.
3. Constraint IDs trong `model/constraints.yaml` có enforcement rõ (hard hoặc warn+audit).
4. ERD trong `doc/system_architecture.md` và `model/entities.yaml` luôn đồng bộ (audit script pass).
5. Mỗi domain có test tích hợp tối thiểu cho happy path + failure gates.

---

## Phase A — Stabilize Foundation (đang làm)

### Mục tiêu
Chốt nền post-award để làm bệ phóng cho full OS.

### Scope
- Hoàn thiện `TenderSnapshot` immutable + hash/version.
- Chuẩn hóa `GenerateExecutionPlan` theo FVTPP.
- Gate pre-activate / pre-delivery / pre-payment với warn-first + override audit.
- `AuditLog` vận hành được cho các action trọng yếu.

### Deliverables
- Resource UI ổn định cho Snapshot/Contract/Gate/Audit.
- Migration an toàn cho `orders`, `order_items`, `audit_logs`, refs cần thiết.
- Feature tests pass cho luồng Snapshot -> Runtime.

---

## Phase B — Core Demand OS (`Order` as Aggregate Root)

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
- Tạo/chuyển trạng thái order không bypass được constraints chính.
- Có trace từ `Order` sang `Contract` runtime và ngược lại.
- Có test cho các chuyển trạng thái hợp lệ/không hợp lệ.

---

## Phase C — Supply + Inventory OS

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
- Ledger phản ánh đúng IN/OUT/RESERVE.
- Reserve có timeout/release policy rõ.
- Có test tình huống thiếu hàng, over-reserve, transfer sai kho.

---

## Phase D — Delivery + Cash OS

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

### Acceptance
- Không thể issue invoice khi thiếu điều kiện.
- Payment readiness đi qua checklist rõ ràng.
- Dashboard tài chính phản ánh đúng aging/gap.

---

## Phase E — Governance, Intelligence, and Hardening

### Mục tiêu
Nâng từ MVP vận hành lên hệ thống bền vững.

### Scope
- Chuyển dần warn-first sang hard gate ở constraint critical.
- Chuẩn hóa command/event log cho audit/replay.
- Auto doc-model consistency check trong CI.
- Data migration plan cho legacy rows không đủ refs.
- Permission matrix theo role/domain (Admin/Sale/Ops/Finance/Warehouse/Procurement).

### Acceptance
- Các constraint critical có enforcement mode rõ.
- CI chặn merge nếu `doc/` và `model/` lệch.
- Có runbook rollback + data backfill cho từng migration lớn.

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

Nếu mục tiêu là build full Business OS, thứ tự ưu tiên hiện tại:
1. Chốt hết phần còn thiếu của **Phase A** (ổn định runtime + audit + UI vận hành).
2. Bắt đầu **Phase B** từ `Order` state machine chuẩn + projection contract.
3. Chỉ sang Supply/Inventory khi `Order` đã là nguồn sự thật duy nhất.


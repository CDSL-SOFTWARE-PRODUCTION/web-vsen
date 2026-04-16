# Implementation Roadmap (Đường ray triển khai)

Mục tiêu của file này: biến `model/*` + `doc/*` thành **backlog có thứ tự** để bạn không “build cả hệ thống cùng lúc”.

Nguyên tắc gốc (ref: `doc/system_architecture.md`)
- **Event + Constraint = Physics**
- **Document là Proof** mở cổng state
- Ưu tiên “máy gợi ý → người Confirm”, không bắt nhập liệu sâu

---

## Scope hiện tại (điểm đứng)

Bạn đã có **Ops Runtime** chạy được trên Filament:
- Tables/Models: `contracts`, `contract_items`, `documents`, `payment_milestones`, `cash_plan_events`, `execution_issues`
- Control tower: widgets + `ContractRiskService`

MVP 1.5 hợp lý nhất: bổ sung lớp **Tender Snapshot → Generate Execution Plan** để “trúng là chạy được”.

---

## Slice 1 — Tender Snapshot (Immutable)

### Mục tiêu
Tạo được “bản chụp” gói thầu/hợp đồng trúng từ muasamcong (metadata + file đính kèm), và **Lock** để bất biến.

### Map sang `model/`
- **Entity**: `TenderSnapshot` (ref: `model/entities.yaml`)
- **Event/Command**: `LockTenderSnapshot` (ref: `model/events.yaml`)
- **Quan hệ**: `Tender → TenderSnapshot` (ref: `model/relations.yaml`)

### UI tối thiểu
- Form tạo snapshot: `source_notify_no`, `source_plan_no`, danh sách file đính kèm
- Action **Lock** (sau Lock: không cho sửa nội dung snapshot)

### Acceptance criteria
- Tạo snapshot từ input thủ công + upload file
- Lock xong snapshot **read-only**
- Có `snapshot_hash`/version để biết snapshot đã thay đổi hay chưa (nếu re-import)

---

## Slice 2 — Generate Execution Plan (Snapshot → Contract Runtime)

### Mục tiêu
Sinh runtime vận hành từ snapshot để Ops chạy ngay: `Contract` + `ContractItem` + checklist `Document` + `PaymentMilestone`.

### Map sang `model/`
- **Event/Command**: `GenerateExecutionPlan` (ref: `model/events.yaml`)
- **Entity runtime**: `Contract`, `ContractItem` (ref: `model/entities.yaml`)
- **Quan hệ**: `Order → Contract (1-1)` ở dài hạn; MVP tối thiểu giữ `tender_snapshot_ref` (ref: `model/relations.yaml`)

### UI tối thiểu
- 1 action “Generate plan” từ snapshot
- 1 màn hình review: line-items và checklist trước khi Activate

### Acceptance criteria
- Generate tạo đủ:
  - `contracts` (project runtime)
  - `contract_items` (line-items)
  - `documents` (template checklist theo profile)
  - `payment_milestones` (mốc thu)
- Ops vào dashboard thấy ngay “đỏ/vàng/xanh” theo cache

---

## Slice 3 — 3 Gates vận hành (Physics chạy thật)

### Mục tiêu
Chặn đúng “cổng”: chưa đủ hồ sơ thì không cho đi tiếp.

### Gate 1: Pre-commit / Activate
- Điều kiện: snapshot lock + items map đủ + checklist seed đủ

### Gate 2: Pre-delivery
- Điều kiện: `docs_status != missing` và không có issue `DocMissing/Quality` mở
- Liên hệ model: `C-EXE-003` (hiện warn) → nếu cần “hard gate” thì tạo constraint hard tương đương

### Gate 3: Pre-payment
- Điều kiện: milestone checklist complete thì mới `payment_ready`
- Liên hệ model: `C-EXE-004` (hiện warn) → nếu cần “hard gate” thì tạo constraint hard tương đương

### Acceptance criteria
- Mỗi gate có:
  - rule rõ ràng
  - thông báo lỗi rõ ràng
  - audit log khi override (nếu có)

---

## Slice 4 — Import Audit Trail (Data lineage)

### Mục tiêu
Truy vết “field nào lấy từ đâu”, ai confirm, lúc nào lock.

### Map sang `model/`
- **Entity**: `AuditLog` (ref: `model/entities.yaml`)

### Acceptance criteria
- Log: lock snapshot, generate plan, update status, upload/validate docs, resolve issues
- Có thể trace từ Contract runtime ngược về snapshot/file nguồn

---

## Slice 5 — Core `Order` (khi Ops đã ổn định)

### Mục tiêu
Triển khai đúng state machine `Order` như `model/states.yaml` (SubmitTender → AwardTender → ConfirmContract…).

### Map sang `model/`
- `Order`, `OrderItem` (entities)
- events: `SubmitTender`, `AwardTender`, `ConfirmContract`, `StartExecution`, ...
- constraints: `C-ORD-*`, `C-FIN-*`, ...

### Acceptance criteria
- `Order` là aggregate root đúng nghĩa
- `Contract` trở thành projection “đúng bài” (1-1 với `Order`)
- Bắt đầu migrate dần: add `order_id` sang các bảng runtime phụ nếu cần (issues/milestones/docs)

---

## “Hôm nay làm gì?” (để khỏi loạn)

- Nếu bạn đang ở MVP 1.5: **làm Slice 1 → Slice 2 → Slice 3**.
- Tuyệt đối không nhảy sang Slice 5 khi Slice 2 chưa chạy được “trúng là tạo được runtime”.


# Ma trận Ops (tổng hợp)

Tài liệu gộp **ma trận quyền** Filament và **ma trận triển khai constraint** (`C-*`). Cập nhật khi đổi policy, resource, hoặc guard.

---

## 1. Ma trận quyền theo role (Filament Ops)

Role runtime: `Admin_PM`, `Sale`, `MuaHang`, `Kho`, `KeToan` (đồng bộ `User::role` và [`App\Support\Ops\FilamentAccess`](../app/Support/Ops/FilamentAccess.php)). **Order** / **Invoice** dùng Laravel [`OrderPolicy`](../app/Policies/OrderPolicy.php) / [`InvoicePolicy`](../app/Policies/InvoicePolicy.php) (đăng ký trong `AppServiceProvider`) — cùng tập role với bảng dưới.

| Resource / nhóm | Admin_PM | Sale | MuaHang | Kho | KeToan |
| --- | --- | --- | --- | --- | --- |
| Order, Tender Snapshot, Contract, Document, Execution Issue | Có | Có | Có | Có | Có |
| Delivery | Có | Có | Có | Có | Có |
| Payment milestone, Cash plan event | Có | Có | — | — | Có |
| Invoice, Financial ledger | Có | — | — | — | Có |
| Audit log, Users | Có | — | — | — | — |

Ghi chú:

- **Pháp nhân (`User.legal_entity_id`):** `Order` / `Invoice` bị **global scope** theo pháp nhân cho role `Sale`, `MuaHang`, `Kho`, `KeToan`. `Admin_PM` không bị scope; user không gán pháp nhân không thấy đơn/hóa đơn (an toàn mặc định). Gán pháp nhân trong Filament **Users** (Admin).
- **Giá dòng hàng (`OrderItem.unit_price`):** cột/form chỉ hiển thị cho `Admin_PM`, `Sale`, `KeToan` — **Kho** và **MuaHang** không thấy (mù giá kênh bán theo `doc/business_workflows.md`).
- **Xóa** hồ sơ (delete) trên một số resource vẫn chỉ `Admin_PM` (ví dụ Invoice, Delivery) — xem `canDelete` từng resource.
- **Cổng nghiệp vụ** (fulfillment, milestone khi xuất HĐ) do `config/ops.php` + domain service, không thay thế ma trận UI.

---

## 2. Ma trận constraint (`C-*`)

Ánh xạ [model/constraints.yaml](../model/constraints.yaml) với code. Trạng thái: **implemented** (hard/warn trong PHP), **partial** (chỉ warn hoặc một nhánh), **not_started**.

| ID | Domain | Status | Where |
| --- | --- | --- | --- |
| C-ORD-005 | Order | implemented (hard) | `CustomerCreditGuard` in `ConfirmContractCommandService` |
| C-FIN-002 | Invoice | implemented | `CancelAndReissueInvoiceService` |
| C-PR-001 | OrderItem | partial (warn) | `OrderTransitionService::buildWarnings` on ConfirmContract — price vs `PriceListItem` (threshold `config('ops.price_list_deviation_warn_percent')`) |
| C-FIN-001 | Invoice | partial (hard/warn via gates) | `IssueInvoiceService::assertIssuePreconditions`, `FulfillmentReadiness`; milestone gate `config('ops.gates.invoice_payment_milestone')` |
| C-INV-001 | InventoryLot | partial | Receive path / tests in `ExecutionPlanFlowTest` |
| C-INV-002 | Reservation | partial | `inventory:release-expired-reservations` + `expires_at` on reservations (`config('ops.reserve_ttl_days')`) |
| C-INV-004 | InventoryLot | partial | `ops:rop-scan` — below-threshold lots logged / alerted |
| C-DEL-001 | Delivery | partial | Operational: delivery + proof via `FulfillmentReadiness` / `GateEvaluator` |
| C-AR-001 | Invoice | partial | `MilestoneAgingService`, `days_overdue_cached` |
| C-ORD-001, C-ORD-002, C-ORD-003, … | Order | not_started or partial | Document checklist warnings on several transitions via `OrderTransitionService` / contracts — extend per backlog |

Cập nhật bảng này khi thêm guard hoặc test gắn mã constraint.

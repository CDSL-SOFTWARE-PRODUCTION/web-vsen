# Ops panel — ma trận quyền theo role

Role runtime: `Admin_PM`, `Sale`, `MuaHang`, `Kho`, `KeToan` (đồng bộ `User::role` và [`App\Support\Ops\FilamentAccess`](../app/Support/Ops/FilamentAccess.php)).

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

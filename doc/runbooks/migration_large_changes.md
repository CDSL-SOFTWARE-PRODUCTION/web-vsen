# Runbook: migration lớn và backfill

## Trước khi chạy

- Sao lưu database (snapshot hoặc `pg_dump`).
- Chạy test Ops local: `php artisan test tests/Feature/Ops`.
- Đọc file migration mới trong `database/migrations/` và ghi chú bảng/cột thêm.

## Thứ tự an toàn

1. Deploy code chỉ **additive** (thêm cột/bảng, default hợp lệ).
2. `php artisan migrate` trên staging trước production.
3. Backfill dữ liệu bằng lệnh Artisan tùy chỉnh (từng batch) nếu cần — không sửa trực tiếp production bằng tay hàng loạt.

## Rollback

- `php artisan migrate:rollback --step=1` chỉ khi migration chưa phụ thuộc dữ liệu đã ghi.
- Nếu đã backfill: khôi phục từ backup thay vì rollback mù.

## Kiểm tra sau migrate

- `php artisan test tests/Feature/Ops`
- `python3 scripts/audit_doc_model_consistency.py`

## Backfill (mẫu checklist)

- [ ] Migration chỉ thêm cột/bảng, default an toàn cho dòng cũ.
- [ ] Lệnh Artisan (nếu có) chạy batch `chunkById`, có `--dry-run` nếu cần.
- [ ] Sau backfill: `SELECT` mẫu đối chiếu với kỳ vọng trên staging.
- [ ] Ghi log `AuditLog` hoặc bảng audit nếu thay đổi dữ liệu nhạy cảm.

## Phase E — gate env (tham chiếu)

- `OPS_GATE_CONFIRM_FULFILLMENT` — `warn` \| `hard` (Order `ConfirmFulfillment`).
- `OPS_GATE_INVOICE_PAYMENT_MILESTONE` — `warn` \| `hard` (xuất HĐ: checklist milestone; chứng từ giao hàng vẫn bắt buộc).

Xem [`config/ops.php`](../config/ops.php).

## Order state: runtime vs canonical

- Cột `orders.state` lưu **runtime** (`SubmitTender`, `AwardTender`, …) — đồng bộ với transition service.
- Tên **canonical** trong `model/states.yaml` (`BidSubmitted`, `ContractSigned`, …) dùng cho tài liệu; bảng ánh xạ: [`model/order_state_mapping.yaml`](../model/order_state_mapping.yaml), [`app/Domain/Demand/OrderState.php`](../../app/Domain/Demand/OrderState.php).
- Nếu sau này đổi chuỗi trong DB: migration backfill + cập nhật `Order::transitionTo` và mapping.

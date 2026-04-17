# Post-MVP: backlog tích hợp & capability (ngoài `doc/system_architecture.md` slice Ops)

Mục đích: gom các hạng mục trong blueprint **chưa** làm trong MVP Filament/Ops để đỡ lẫn với “đủ theo phase” trong [`implementation_roadmap.md`](../implementation_roadmap.md).

## 1. Tích hợp kế toán — MISA

- **Mô tả blueprint:** `MisaInvoiceAdapter.issue()` — Core là master; xuất VAT điện tử qua API; lưu `misa_transaction_id` trên `Invoice`.
- **Trạng thái repo:** [`App\Contracts\Finance\MisaInvoicePort`](../../app/Contracts/Finance/MisaInvoicePort.php) + [`NullMisaInvoiceAdapter`](../../app/Services/Finance/NullMisaInvoiceAdapter.php) đã bind trong `AppServiceProvider`. Bật `MISA_INTEGRATION_ENABLED=true` trong [`.env`](../../.env.example) để stub ghi `misa_transaction_id` (dev). Production: thay binding bằng client HTTP thật + retry/idempotency.

## 2. Ngân hàng — đối soát Virtual Account

- **Mô tả blueprint:** Webhook POST → map VA (`9999` + mã hợp đồng) → ghi nhận thanh toán / giảm nợ.
- **Trạng thái repo:** route POST [`/webhooks/bank-virtual-account`](../../routes/web.php) → [`BankVirtualAccountWebhookController`](../../app/Http/Controllers/Webhooks/BankVirtualAccountWebhookController.php) (stub; bật `BANK_VA_WEBHOOK_ENABLED`). CSRF excluded trong [`bootstrap/app.php`](../../bootstrap/app.php).
- **Việc cần làm:** verify signature, idempotency key, map VA → `Contract`/`Invoice`/`PaymentMilestone`, audit.

## 3. Engineering core — EventBus (domain events)

- **Mô tả blueprint:** Sau command → publish event cho domain khác (thay vì chỉ gọi service trực tiếp).
- **Trạng thái repo:** [`InvoiceIssued`](../../app/Events/InvoiceIssued.php) được dispatch sau khi xuất HĐ; có thể đăng ký listener/queue. Mở rộng dần theo `model/events.yaml`.
- **Việc cần làm:** listeners nghiệp vụ, outbox nếu cần đảm bảo giao dịch.

## 4. Knowledge layer (LLM, vector, canonical product)

- **Mô tả blueprint:** Raw tender/file → normalize → semantic search; Product Knowledge 3 tầng.
- **Trạng thái repo:** `TenderSnapshot` + lock phục vụ Ops; không có pipeline LLM/vector trong app.
- **Việc cần làm:** backlog riêng (ingestion, embedding store, UI review confidence).

## 5. Planning & Logistics engine (ROP, cron, tối ưu vận tải)

- **Mô tả blueprint:** ROP, ABC, cron đêm, tối ưu chi phí vận tải — phần lớn là chiến lược trong `system_architecture.md`.
- **Trạng thái repo:** lệnh stub [`ops:rop-scan`](../../app/Console/Commands/RopScanCommand.php) (chưa nối rule). Inventory/supply có service.
- **Việc cần làm:** scheduled commands + ngưỡng theo `Product`/`Partner` + báo cáo gợi ý PO.

---

Cập nhật khi một mục được chuyển vào roadmap chính hoặc đóng bằng PR cụ thể.


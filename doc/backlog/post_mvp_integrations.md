# Post-MVP: tích hợp ngoài & Intelligence (hoãn cho đến khi logic nội bộ chặt)

File này gom các **tích hợp bên ngoài** và **lớp Intelligence** không nằm trong ưu tiên hiện tại. Roadmap chính: [`doc/implementation_roadmap.md`](../implementation_roadmap.md) — ưu tiên **nhất quán logic doanh nghiệp** (model, constraint, command, test) trước.

## Nguyên tắc

- Giữ **port/interface** trong code (ví dụ `MisaInvoicePort`) + **null adapter** để môi trường dev/CI không phụ thuộc vendor.
- Không coi **MISA**, **webhook ngân hàng / VA**, hay LLM/vector là blocker cho việc hoàn thiện Order → Supply → Inventory → Delivery → Cash **trong nội bộ**.

## Backlog (tham chiếu `doc/system_architecture.md`)

| Hạng mục | Ghi chú |
| --- | --- |
| MISA e-invoice | Adapter thật + env sandbox khi có API ổn định |
| Bank VA / auto reconciliation | Webhook idempotent + ledger; sau khi luồng nội bộ đã test kỹ |
| Tender Intelligence (LLM, vector) | Sau pipeline file → snapshot đã rõ |
| Event bus ngoài (queue consumer khác) | Tùy scale; domain events nội bộ đủ cho MVP |

## Khi nào kéo vào sprint

Khi `model/states.yaml` đã reconcile với runtime, ma trận `C-*` đã có enforce + test, và `scripts/audit_doc_model_consistency.py` xanh ổn định trên nhánh chính.

# 🛠️ HƯỚNG DẪN PHÁT TRIỂN (DEVELOPMENT GUIDE)

Tài liệu này là "Kim chỉ nam" duy nhất cho lập trình viên. Dự án này không phải website thông thường, nó là một **Business OS (ERP/CRM)** vận hành trên triết lý **Axiom: Event + Constraint = Business Physics**.

---

## 1. KHỞI TẠO NHANH (LOCAL SETUP)

Làm theo hướng dẫn tại [README.md](./README.md).

**Lưu ý quan trọng về Port:**
- **Database/Redis:** Chạy trong Docker (Port 5433, 6379).
- **Backend PHP:** Chạy qua `php artisan serve` (Port 8000).
- **Frontend Vite:** Chạy qua `npm run dev` (Port 5173).

---

## 2. TRIẾT LÝ VẬN HÀNH (CORE ARCHITECTURE)

Hệ thống được chia làm 5 lớp (Layers) liên kết chặt chẽ bằng **Events**:

1.  **Demand (Nhu cầu - Sale):** Quản lý thầu, báo giá. Entity chính: `Order`.
2.  **Supply (Nguồn cung - Mua hàng):** Nhập hàng từ NCC, dán tem phụ. Entity chính: `SupplyOrder`.
3.  **Inventory (Kho):** Quản lý lô (batch), hạn dùng. Tự động lock hàng cho đơn thầu. Entity chính: `InventoryLot`.
4.  **Delivery (Giao nhận):** Tài xế chụp ảnh minh chứng (Proof of Delivery). Entity chính: `Delivery`.
5.  **Cash (Dòng tiền - Kế toán):** Chỉ cho phép xuất hóa đơn khi có minh chứng giao hàng. Entity chính: `Invoice`, `Ledger`.

---

## 3. QUY TRÌNH PHÁT TRIỂN TÍNH NĂNG (MDD WORKFLOW)

Tuyệt đối KHÔNG gõ code ngay. Hãy tuân thủ quy trình **Model-First**:

### Bước 1: Định nghĩa Model (YAML)
Mọi Logic nghiệp vụ nằm ở thư mục `model/`:
- `entities.yaml`: Khai báo bảng và các trường dữ liệu.
- `states.yaml`: Định nghĩa các trạng thái (Draft -> Confirmed -> Shipped).
- `constraints.yaml`: Các điều kiện chặn (Ví dụ: "Không được xuất kho nếu chưa đủ giấy tờ").

### Bước 2: Sinh Code & UI
- **Backend:** Tạo Migration và Model (Nên gộp theo Module như `app/Models/HR`, `app/Models/Inventory`).
- **Admin UI:** Dùng **Filament v3**. Ưu tiên dùng `Cluster` để nhóm các màn hình liên quan.
- **Client UI:** Dùng **React + Inertia.js**. Toàn bộ giao diện nằm trong `resources/js/Pages`.

---

## 4. MA TRẬN QUYỀN (ROLE MATRIX)

| Role | Quyền hạn chính | Điều hạn chế |
| :--- | :--- | :--- |
| **Sale** | Xem giá bán, tạo đơn khách mình. | KHÔNG thấy giá vốn NCC. |
| **Kho** | Quét mã, xuất/nhập lô, in phiếu. | "Mù giá": KHÔNG thấy bất kỳ thông tin tiền bạc nào. |
| **Kế toán** | Xuất VAT, đối soát tiền về. | Chỉ thấy dữ liệu của pháp nhân mình quản lý. |
| **Founder** | Duyệt lệnh lách luật, xem lợi nhuận gộp. | Thấy toàn bộ 4 pháp nhân của công ty. |

---

## 5. TECH STACK CỐT LÕI

- **Framework:** Laravel 12.
- **Admin Panel:** Filament v3 (Tốc độ phát triển nhanh cho ERP).
- **Frontend:** React 18 + Tailwind CSS (Trải nghiệm mượt cho khách hàng).
- **Database:** PostgreSQL (Mạnh về JSON và ràng buộc dữ liệu).

---

## 6. LƯU Ý KHI CODE

- **Audit Log:** Mọi thay đổi State của Entity phải được ghi log (Ai làm, lúc nào, giá trị cũ/mới).
- **Constraint Engine:** Luôn kiểm tra điều kiện tại Service Layer trước khi lưu vào Database.
- **Migration:** Trên Dev có thể `migrate:fresh`. Trên Prod chỉ được dùng migration thêm mới (Additive).

---
*DVT Enterprise OS — Bảo mật nội bộ.*

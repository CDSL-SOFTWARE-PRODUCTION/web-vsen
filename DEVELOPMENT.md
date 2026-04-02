# Hướng Dẫn Phát Triển (Development Guide) - Business OS

Dự án này đang chuyển mình từ một Website Sản phẩm thông thường sang một **Business OS (ERP/CRM)** toàn diện dành cho VSEN Medical.

Tài liệu này đóng vai trò là "kim chỉ nam" cho mọi lập trình viên khi tham gia phát triển và duy trì hệ thống.

---

## 1. Khởi tạo Dự án (Local Development)

Để chạy dự án ở môi trường Local lần đầu tiên, hãy làm theo các bước sau. Dự án hỗ trợ cả chạy native hoặc dùng Laravel Sail (Docker).

### Cài đặt Dependencies
```bash
# Cài đặt PHP packages
composer install

# Cài đặt Node packages (cho Frontend React/Inertia)
npm install
```

### Cấu hình Môi trường
```bash
# Tạo file .env từ template
cp .env.example .env

# Tạo application key
php artisan key:generate
```

### Chạy Database và Serve
Nếu bạn đã cài sẵn PostgreSQL ở Local:
```bash
# Khởi tạo các bảng và Bơm dữ liệu giả (Bắt buộc để test UI)
php artisan migrate:fresh --seed

# Bật chạy ứng dụng Backend
php artisan serve

# (Mở Terminal khác) Bật chạy Frontend Vite để hot-reload
npm run dev
```

Nếu dùng Docker (Laravel Sail):
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm run dev
```

> **Lưu ý:** Sau khi chạy seeder, bạn sẽ có sẵn 1 tài khoản Admin là `admin@vsen.com` / Pass: `password` và danh sách 5 thiết bị y tế giả lập sẵn.

---

## 2. Quy Trình Làm Việc (The MDD Workflow)

Dự án áp dụng phương pháp **Model-Driven Documentation (MDD)**. Mọi thứ bắt đầu từ thư mục `model/` ở Root project.

**KHI BẠN NHẬN ĐƯỢC YÊU CẦU LÀM TÍNH NĂNG MỚI (Ví dụ: Thêm tính năng "Quản lý Hợp đồng"):**

1. **Planning:** Mở file `model/entities.yaml` và định nghĩa Entity `Contract` với các trường dữ liệu cần thiết. Nếu Hợp đồng liên quan đến `Employee`, hãy khai báo nó ở `model/relations.yaml`.
2. **Migrations & Models:** Tạo Laravel Model dựa theo YAML đã định nghĩa, và luôn luôn gộp nó theo **Nghiệp vụ (Domain)**:
   ```bash
   php artisan make:model HR/Contract -m
   ```
3. **UI Generation (Cho Admin/Staff):** Hầu hết các ứng dụng của Business OS nên được xây dựng bằng Filament. Chạy lệnh để sinh ra giao diện:
   ```bash
   php artisan make:filament-resource HR/Contract --cluster=HrCluster
   ```

Tuyệt đối KHÔNG gõ code (Controller/Blade cũ) trước khi cập nhật các file trong thư mục `model/`. Thư mục này là **Single Source of Truth**.

---

## 3. Tech Stack cốt lõi
Để giữ hệ thống nguyên khối ổn định, hạn chế cài các công nghệ bên ngoài nếu không thật sự cần thiết. Hệ thống OS chúng ta dùng 100% stack mặc định:

✅ **Backend Quản Nội Bộ:** Laravel 12 + Filament v3 (Sử dụng Clusters, Infolist, Custom Pages)
✅ **Frontend Khách Hàng:** React 18 + Inertia.js + TailwindCSS + Vite
✅ **Database:** PostgreSQL (Mạnh mẽ về xử lý JSON, Constraints và Full-Text Search)

---

## 4. Quản lý Database

*   **Môi trường Dev:** Cứ thoải mái thiết kế tính năng mới và gõ `php artisan migrate:fresh --seed` để có database chuẩn. Hãy luôn tạo Factory cho Model mới.
*   **Môi trường Production:** Chặn tuyệt đối lệnh migrate fresh. Chúng ta dùng **Additive Migrations** (Tạo thêm các file migration mới để alter update bảng cũ) thay vì sửa chữa lại file migration ban đầu.

---
*Tài liệu kế thừa và lược bỏ từ hệ thống cũ nhằm mục tiêu tinh gọn cho Business OS.*

# 🛡️ VSEN Medical — MASTER BLUEPRINT

> **"Business Logic as Code. Physics for your Enterprise."**  
> Đây là bản thiết kế hệ thống (Blueprints) cho Hệ điều hành Doanh nghiệp DVT — Một nền tảng quản trị thông minh, tự động hóa rủi ro và tối ưu hóa vận hành.

---

## 🚀 CÀI ĐẶT NHANH (QUICK START)

Copy và paste toàn bộ khối lệnh dưới đây vào Terminal của bạn (Yêu cầu đã cài Docker):

```bash
# 1. Tạo file môi trường
cp .env.example .env

# 2. Cài đặt thư viện (PHP & JS)
composer install && npm install

# 3. Khởi động Docker (Laravel Sail)
./vendor/bin/sail up -d

# 4. Cấu hình App & Database
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed

# 5. Chạy dev
composer dev
```

**Sau khi chạy xong:**
- 🌐 Truy cập: `http://localhost:8000`
- 📧 Tài khoản Admin mặc định: (Xem trong `DatabaseSeeder.php` hoặc mặc định là `admin@vsen.com` / `password`)

---

## 💎 TRIẾT LÝ CỐT LÕI (CORE PHILOSOPHY)

Hệ thống hoạt động theo tiên đề: **Event + Constraint = Business Physics**.

- **Model là SSOT (Single Source of Truth):** Mọi quy tắc nghiệp vụ (Luật), trạng thái (State), và thực thể (Entity) được định nghĩa tại thư mục `model/` dưới dạng YAML.
- **Narrative giải thích "Tại sao":** Xem [`doc/README.md`](doc/README.md) — chủ yếu [`doc/guide.md`](doc/guide.md) (vận hành) và [`doc/system_architecture.md`](doc/system_architecture.md) (blueprint + ERD).
- **Constraint-Based Control:** Hệ thống tự động ngăn chặn rủi ro thông qua bộ lọc (Constraints) được định nghĩa sẵn.

---

## 🧭 HƯỚNG DẪN CHI TIẾT

Để hiểu sâu hơn về kiến trúc hệ thống và bắt đầu phát triển tính năng mới, bạn **BẮT BUỘC** phải đọc:

👉 **[`doc/README.md`](doc/README.md)** — mục lục; **[`doc/guide.md`](doc/guide.md)** (roadmap, ma trận `C-*`, backlog); **[`doc/system_architecture.md`](doc/system_architecture.md)** (blueprint + ERD).

- **Triết lý ERP:** Event + Constraint = Business Physics (`model/`, `doc/system_architecture.md`).
- **Phân quyền / Ops:** `doc/guide.md` (ma trận Filament).

---

## 📁 CẤU TRÚC DỰ ÁN

```bash
DVT-company/
├── model/                ← 📚 Triết lý & Cấu hình (YAML)
├── doc/                  ← 📖 guide.md + system_architecture.md
├── app/                  ← 🏗️ Backend (Laravel)
├── resources/js/         ← 🏗️ Frontend (React/Inertia)
├── database/             ← 🗄️ Database (Migrations & Seeds)
```

---

**© 2026 DVT Enterprise OS Team.**  
*CONFIDENTIAL — Nội bộ Công ty DVT.*


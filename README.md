# 🛡️ DVT ENTERPRISE OS — MASTER BLUEPRINT

> **"Business Logic as Code. Physics for your Enterprise."**  
> Đây là bản thiết kế hệ thống (Blueprints) cho Hệ điều hành Doanh nghiệp DVT — Một nền tảng quản trị thông minh, tự động hóa rủi ro và tối ưu hóa vận hành dựa trên mô hình **Model-Driven Documentation (MDD)**.

---

## 💎 TRIẾT LÝ CỐT LÕI (CORE PHILOSOPHY)

Hệ thống hoạt động theo tiên đề: **Event + Constraint = Business Physics**.

- **Model là SSOT (Single Source of Truth):** Mọi quy tắc nghiệp vụ (Luật), trạng thái (State), và thực thể (Entity) được định nghĩa tại thư mục `model/` dưới dạng YAML để máy tính có thể đọc, kiểm tra và mô phỏng.
- **Narrative giải thích "Tại sao":** Tài liệu tại `doc/` không lặp lại Spec kỹ thuật mà tập trung giải thích ngữ cảnh kinh doanh, trải nghiệm người dùng và lộ trình chiến lược.
- **Constraint-Based Control:** Hệ thống tự động ngăn chặn rủi ro (hóa đơn sai, xuất kho thừa, nợ xấu) thông qua 17+ bộ lọc (Constraints) được định nghĩa sẵn.

---

## 📁 CẤU TRÚC DỰ ÁN (PROJECT REPOSITORY)

```bash
DVT-company/
├── model/                ← 📚 SOURCE OF TRUTH (YAML Models)
├── doc/                  ← 📖 NARRATIVE & STRATEGY (Hành trình nghiệp vụ)
│   ├── report.md         ← Báo cáo cho Founder
│   ├── system_architecture.md ← Kiến trúc kỹ thuật
│   └── business_workflows.md  ← Luồng nghiệp vụ
├── scripts/              ← 🛠️ AUTOMATION & VALIDATION
├── docs-legacy/          ← 📂 TÀI LIỆU WEBSITE CŨ (Requirements & Deployment)
├── app/, resources/, ... ← 🏗️ SOURCE CODE (Laravel + React)
├── package.json          ← Node scripts & dependencies
└── README.md             ← Bạn đang ở đây
```

---

## 🧭 CÁCH ĐỌC VÀ SỬ DỤNG (HOW TO EXPLORE)

### 1. Dành cho Developer / Architect (Hệ thống tương lai)
Hãy bắt đầu từ thư mục `model/` để nắm bắt "Xương sống" của hệ thống mới:
- [entities.yaml](model/entities.yaml), [states.yaml](model/states.yaml), [constraints.yaml](model/constraints.yaml).

### 2. Dành cho Business Analyst / Manager
Hãy đọc thư mục `doc/` để hiểu "Hơi thở" của doanh nghiệp:
- [business_workflows.md](doc/business_workflows.md), [system_architecture.md](doc/system_architecture.md).

### 3. Dành cho Founder
- [report.md](doc/report.md): Bản thuyết minh chiến lược và lộ trình triển khai.

---

## 🚀 KỸ THUẬT & TRIỂN KHAI (ENGINEERING & DEPLOYMENT)

Phần này dành cho việc phát triển và vận hành website hiện tại.

### Cài đặt nhanh (Quick Start)
```bash
composer install
npm install
php artisan migrate
npm run dev
```

### Tài liệu lập trình chi tiết
- **Hướng dẫn Setup & Env:** Xem bên dưới hoặc [docs-legacy/README.md](docs-legacy/README.md)
- **Quy trình Deploy:** [docs-legacy/DEPLOYMENT.md](docs-legacy/DEPLOYMENT.md)
- **Yêu cầu hệ thống:** PHP 8.2+, Node 20+, PostgreSQL 15+, Redis 7+.

---

## ⚡ HỆ THỐNG KIỂM SOÁT (VALIDATION ENGINE)
Dự án tích hợp sẵn bộ cụ kiểm tra tự động để đảm bảo tài liệu không bị sai lệch:
```bash
# Kiểm tra tính nhất quán giữa các file Model (YAML)
npm run validate

# Kiểm tra độ bao phủ và tính chính xác của Báo cáo
npm run validate:report
```

---

## 🛠️ QUY TRÌNH THAY ĐỔI (CONTRIBUTION WORKFLOW)
1. **Model First:** Thay đổi logic trong `model/*.yaml`.
2. **Validate:** Chạy `npm run validate`.
3. **Narrative Update:** Cập nhật nội dung trong `doc/*.md`.
4. **Final Sync:** Chạy `npm run validate:report`.

---

**© 2026 DVT Enterprise OS Team.**  
*CONFIDENTIAL — Nội bộ Công ty DVT.*

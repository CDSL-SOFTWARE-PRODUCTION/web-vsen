# Tài liệu Dự án - VSEN Medical Equipment Website

**Version:** 1.0  
**Date:** 2025-12-28

---

## Tổng quan

Thư mục này chứa tài liệu cho dự án website bán thiết bị y tế, được tối giản cho team nhỏ.

**Cấu trúc tài liệu:**
1. **01-Requirements.md** - Requirements, User Stories, Phase Comparison
2. **02-Technical.md** - Architecture, Database, API, Infrastructure
3. **03-Design.md** - Sitemap, Design System
4. **diagrams/** - System Diagrams (UML, ERD, Sequence, Activity, etc.)

---

## Danh sách Tài liệu

### 📄 [01-Requirements.md](./01-Requirements.md)
**Requirements & User Stories**

Bao gồm:
- Phân chia Phase 1 vs Phase 2
- Product Requirements (Phase 1)
- User Stories & Backlog (Phase 1)
- Sprint planning

**Đối tượng:** Product Owner, Development Team, Scrum Master

---

### 📄 [02-Technical.md](./02-Technical.md)
**Technical Documentation**

Bao gồm:
- System Architecture
- Technology Stack
- Database Schema (Phase 1)
- API Documentation
- Infrastructure & Deployment

**Đối tượng:** Backend Developers, Frontend Developers, DevOps

---

### 📄 [03-Design.md](./03-Design.md)
**Design Documentation**

Bao gồm:
- Sitemap & Information Architecture
- Design System (Colors, Typography, Components)
- Responsive Design

**Đối tượng:** UI/UX Designers, Frontend Developers

---

## Cách sử dụng

### Cho Product Owner
1. Đọc **01-Requirements.md** để hiểu requirements, user stories và Phase 1/2 scope

### Cho Development Team
1. Đọc **01-Requirements.md** để hiểu requirements
2. Đọc **02-Technical.md** để implement
3. Tham khảo **03-Design.md** cho UI/UX
4. Xem **diagrams/** để hiểu system architecture và data flow

### Cho Designers
1. Đọc **01-Requirements.md** để hiểu features
2. Đọc **03-Design.md** để thiết kế theo design system
3. Xem **diagrams/** để hiểu user flows và system interactions

---

## Quick Start

### Development
```bash
composer install
npm install
php artisan migrate
npm run dev
```

### Deployment
Xem chi tiết trong **02-Technical.md**

---

## Diagrams

Xem **[Diagrams Overview](./diagrams/00-Diagrams-Overview.md)** để có hướng dẫn đầy đủ về các diagram:

- **[Quick Overview](./diagrams/01-Quick-Overview.md)** ⭐ - **ĐỌC ĐẦU TIÊN** (5-10 phút)
- **[Flowchart](./diagrams/02-Flowchart.md)** - Luồng xử lý của Visitor và Admin
- **[Sequence Diagrams](./diagrams/03-Sequence-Diagrams.md)** - Tương tác giữa components
- **[Class Diagram](./diagrams/04-Class-Diagram.md)** - Cấu trúc classes và models
- **[State Diagram](./diagrams/05-State-Diagram.md)** - State machines cho entities
- **[ERD](./diagrams/06-ERD.md)** - Database schema và relationships
- **[User Journey](./diagrams/07-User-Journey.md)** - Hành trình người dùng

Tất cả diagrams sử dụng **Mermaid** syntax, có thể xem trực tiếp trong VS Code (với Mermaid extension) hoặc online tại https://mermaid.live/

## Notes

- Tài liệu được tối giản cho team nhỏ
- Focus vào Phase 1 (MVP)
- Phase 2 features sẽ được thêm sau
- Tất cả tài liệu sử dụng Markdown format
- Diagrams sử dụng Mermaid syntax

---

**Last Updated:** 2025-12-28

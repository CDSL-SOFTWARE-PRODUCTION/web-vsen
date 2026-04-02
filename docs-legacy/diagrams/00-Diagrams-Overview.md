# Diagrams Overview - VSEN Medical Equipment Website

**Version:** 1.0  
**Date:** 01-01-2026

---

## 🚀 Bắt Đầu Từ Đây

### [📊 Quick Overview](./01-Quick-Overview.md) ⭐ **ĐỌC FILE NÀY LÀ ĐỦ**

**5-10 phút đọc** - Giúp mọi người hiểu đầy đủ:
- ✅ App đang làm gì?
- ✅ Luồng người dùng chính
- ✅ Cấu trúc hệ thống
- ✅ Features chính (Phase 1)

👉 **File này đã đủ để hiểu toàn bộ app và requirements!**

---

## 📚 Detailed Diagrams - Chi Tiết Theo Loại

Các diagram dưới đây được tổ chức theo 6 loại chính để mô tả toàn bộ project:

### Danh Sách Diagrams

| Diagram | File | Mục đích |
|---------|------|----------|
| **Flowchart** | [02-Flowchart.md](./02-Flowchart.md) | Mô tả luồng xử lý của Visitor và Admin (User Flow, Product Management, Lead Management) |
| **Sequence Diagram** | [03-Sequence-Diagrams.md](./03-Sequence-Diagrams.md) | Mô tả tương tác giữa các components trong các use cases chính (Search, View Product, Request Quote, Admin operations) |
| **Class Diagram** | [04-Class-Diagram.md](./04-Class-Diagram.md) | Mô tả cấu trúc classes, models và relationships trong codebase |
| **State Diagram** | [05-State-Diagram.md](./05-State-Diagram.md) | Mô tả state machine cho Quote Request, Article, và Product status |
| **ERD** | [06-ERD.md](./06-ERD.md) | Mô tả database schema, entities, relationships, và constraints |
| **User Journey** | [07-User-Journey.md](./07-User-Journey.md) | Mô tả hành trình người dùng (Visitor và Admin) qua các touchpoints |

### Khi Nào Cần Xem Diagram Chi Tiết?

**Cho Hầu Hết Mọi Người:**
- ✅ **Chỉ cần đọc Quick Overview** là đủ để hiểu tổng quan

**Cho Developers Khi Implement:**
- ✅ **Flowchart** - Khi cần hiểu business flow và user journey
- ✅ **Sequence Diagram** - Khi implement API endpoints và cần hiểu luồng tương tác
- ✅ **Class Diagram** - Khi thiết kế/refactor code structure
- ✅ **State Diagram** - Khi implement state management cho entities
- ✅ **ERD** - Khi tạo migrations và thiết kế database schema
- ✅ **User Journey** - Khi thiết kế UX và cải thiện user experience

---

## Diagram Format

Tất cả diagrams được viết bằng **Mermaid** syntax để có thể:
- Render trực tiếp trong GitHub/GitLab
- Render trong VS Code với Mermaid extension
- Export sang PNG/SVG bằng Mermaid CLI

**Cách xem:**
1. Cài Mermaid extension trong VS Code
2. Hoặc xem online tại: https://mermaid.live/
3. Hoặc dùng Mermaid CLI: `npm install -g @mermaid-js/mermaid-cli`

---

## Quick Links

### ⭐ **BẮT BUỘC ĐỌC**
- [Quick Overview](./01-Quick-Overview.md) - Tổng quan về app và requirements

### 📋 Chi Tiết Theo Loại Diagram

1. **[Flowchart](./02-Flowchart.md)** - Luồng xử lý của Visitor và Admin
2. **[Sequence Diagrams](./03-Sequence-Diagrams.md)** - Tương tác giữa components
3. **[Class Diagram](./04-Class-Diagram.md)** - Cấu trúc classes và models
4. **[State Diagram](./05-State-Diagram.md)** - State machines cho entities
5. **[ERD](./06-ERD.md)** - Database schema và relationships
6. **[User Journey](./07-User-Journey.md)** - Hành trình người dùng

---

## Notes

- **Quick Overview đã đủ** để hiểu tổng quan app và requirements
- Các diagram chi tiết được tổ chức theo 6 loại chính (Flowchart, Sequence, Class, State, ERD, User Journey)
- Tất cả diagrams focus vào **Phase 1 (MVP)** only
- Không có trùng lặp - mỗi diagram có mục đích riêng
- Nếu không chắc cần diagram nào → chỉ cần đọc Quick Overview

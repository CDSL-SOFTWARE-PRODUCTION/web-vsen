# Quick Overview Diagrams - VSEN Medical Equipment Website

**Mục đích:** Giúp mọi người hiểu nhanh app đang làm gì và các yêu cầu chính

---

## 1. System Overview - App Làm Gì?

```mermaid
graph TB
    subgraph "App này làm gì?"
        Visitor[👤 Visitor<br/>Khách hàng tiềm năng]
        Admin[👨‍💼 Admin<br/>Quản lý nội dung]
    end
    
    subgraph "Chức năng chính (Phase 1)"
        Products[📦 Products<br/>Danh mục sản phẩm<br/>Y tế]
        Search[🔍 Search<br/>Tìm kiếm sản phẩm,<br/>bài viết, tài liệu]
        Content[📰 Content<br/>Tin tức, Tài liệu<br/>chuyên môn]
        Leads[📋 Leads<br/>Thu thập yêu cầu<br/>báo giá]
    end
    
    subgraph "Kết quả"
        Sales[💰 Sales Team<br/>Nhận leads để<br/>bán hàng]
    end
    
    Visitor -->|Xem, tìm kiếm| Products
    Visitor -->|Tìm kiếm| Search
    Visitor -->|Đọc| Content
    Visitor -->|Gửi yêu cầu| Leads
    Leads -->|Email thông báo| Sales
    
    Admin -->|Quản lý| Products
    Admin -->|Quản lý| Content
    Admin -->|Xem và xử lý| Leads
```

**Tóm tắt:** Website bán thiết bị y tế - Visitor xem sản phẩm, tìm kiếm, đọc tin tức → Gửi yêu cầu báo giá → Sales team follow up.

---

## 2. User Flow - Luồng Người Dùng Chính

```mermaid
flowchart TD
    Start([Visitor vào website])
    
    Start --> HomePage[🏠 Trang chủ]
    HomePage --> Choose{Chọn hành động}
    
    Choose -->|Tìm sản phẩm| Search[🔍 Tìm kiếm]
    Choose -->|Xem danh mục| Browse[📦 Duyệt sản phẩm]
    Choose -->|Đọc tin tức| News[📰 Tin tức/Tài liệu]
    
    Search --> ProductList[📋 Danh sách sản phẩm]
    Browse --> ProductList
    ProductList --> ProductDetail[📄 Chi tiết sản phẩm]
    
    ProductDetail --> ViewInfo[Xem thông tin:<br/>- Hình ảnh<br/>- Thông số kỹ thuật<br/>- Tài liệu PDF]
    ViewInfo --> Action{Quyết định?}
    
    Action -->|Quan tâm| RequestQuote[📝 Yêu cầu báo giá]
    Action -->|Tìm thêm| ProductList
    Action -->|Xem tin tức| News
    
    RequestQuote --> FillForm[Điền form:<br/>Tên, Email, Phone,<br/>Company, Số lượng]
    FillForm --> Submit[Gửi yêu cầu]
    Submit --> Success[✅ Thành công<br/>Sales team nhận email]
    
    News --> ArticleDetail[📄 Chi tiết bài viết]
    ArticleDetail --> Download[📥 Download tài liệu]
    
    Success --> End([Kết thúc])
    Download --> End
```

**Tóm tắt:** Visitor tìm/xem sản phẩm → Xem chi tiết → Nếu quan tâm thì yêu cầu báo giá → Sales team nhận thông tin.

> 💡 **Chi tiết hơn:** Xem [Flowchart Diagrams](./02-Flowchart.md) để hiểu rõ các luồng chi tiết

---

## 3. System Architecture - Cấu Trúc Đơn Giản

```mermaid
graph TB
    subgraph "Frontend (React)"
        UI[Giao diện người dùng<br/>React + Inertia]
    end
    
    subgraph "Backend (Laravel)"
        API[API/Controllers<br/>Xử lý request]
        Logic[Business Logic<br/>Services]
    end
    
    subgraph "Database & Storage"
        DB[(PostgreSQL<br/>Lưu dữ liệu)]
        Files[File Storage<br/>Ảnh, PDF]
        Cache[(Redis<br/>Cache)]
        Search[(Elasticsearch<br/>Tìm kiếm)]
    end
    
    subgraph "External"
        Email[Email Service<br/>Gửi thông báo]
    end
    
    UI -->|HTTP Request| API
    API --> Logic
    Logic --> DB
    Logic --> Files
    Logic --> Cache
    Logic --> Search
    Logic --> Email
    
    style UI fill:#e1f5ff
    style API fill:#ffe1e1
    style DB fill:#e1ffe1
```

**Tóm tắt:** React frontend → Laravel backend → Database/Storage → Kết quả trả về cho người dùng.

> 💡 **Chi tiết hơn:** Xem các [Sequence Diagrams](./03-Sequence-Diagrams.md) để hiểu rõ luồng tương tác giữa các components

---

## 4. Key Features Summary

### Visitor Có Thể:
✅ Tìm kiếm sản phẩm (Search với auto-complete)  
✅ Xem danh sách sản phẩm (Grid/List, Filter, Sort)  
✅ Xem chi tiết sản phẩm (Ảnh, Specs, PDF downloads)  
✅ Đọc tin tức và tài liệu chuyên môn  
✅ Yêu cầu báo giá (Request Quote)  
✅ Gửi liên hệ (Contact Form)  

### Admin Có Thể:
✅ Quản lý sản phẩm (CRUD, Upload ảnh/PDF)  
✅ Quản lý tin tức và tài liệu  
✅ Xem và xử lý yêu cầu báo giá (Leads)  
✅ Bulk import/export sản phẩm  

### KHÔNG Có Trong Phase 1:
❌ Đăng ký/Đăng nhập người dùng  
❌ Giỏ hàng và thanh toán  
❌ Đa ngôn ngữ  
❌ Events/Webinars  
❌ CRM Integration  


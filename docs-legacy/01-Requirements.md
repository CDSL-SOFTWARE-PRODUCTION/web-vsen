# Requirements & User Stories

## Website Bán Thiết Bị Y Tế - VSEN Medical Equipment

**Version:** 1.0  
**Date:** 2025-12-28  
**Status:** Draft

---

## Mục lục

1. [Tổng quan Phase 1 vs Phase 2](#phase-comparison)
2. [Product Requirements (Phase 1)](#prd)
3. [User Stories & Backlog (Phase 1)](#user-stories)

---

## <a name="phase-comparison"></a>1. Phân chia Phase 1 và Phase 2

### Phase 1 - MVP (3-4 tháng)

**✅ Có trong Phase 1:**

- Navigation & Search (Mega Menu, Search - **KHÔNG có đa ngôn ngữ**)
- Product Catalog đầy đủ (listing, detail, filters)
- Content Management (News, Resources - **KHÔNG có Events**)
- Admin Panel (Filament) - Quản lý cơ bản
- **Basic Lead Generation:** Quote requests, contact forms (chưa có CRM)
- **Basic Service Request:** Form gửi yêu cầu (chưa có tracking)

**❌ KHÔNG có trong Phase 1:**
- Đa ngôn ngữ & Đa quốc gia
- Events & Webinars
- User Registration/Login
- Customer Portal/My Account
- Shopping cart & checkout
- Order management
- CRM integration
- Chatbot/Live chat

### Phase 2 - Nâng cao (4-6 tháng)

**✅ Sẽ thêm trong Phase 2:**
- Đa ngôn ngữ & Đa quốc gia
- Events & Webinars
- User Authentication & Customer Portal
- B2B E-commerce (Cart, Checkout, Orders)
- Service Request System nâng cao (Tracking)
- CRM Integration
- Advanced Lead Gen (Chatbot, Newsletter)
- Compliance (HIPAA, GDPR, WCAG 2.1)

---

## <a name="prd"></a>2. Product Requirements Document (Phase 1)

### 2.1 Navigation & Search

#### Mega Menu
- **Products:** MRI, CT, Ultrasound, X-Ray, Laboratory, Patient Monitoring
- **Services:** Bảo trì, Sửa chữa, Phụ tùng, Tư vấn
- **Solutions:** Theo khoa (Cardiology, Oncology, etc.)
- **Education:** Đào tạo, Tài liệu (menu only - features in Phase 2)
- **Insights:** News, Case Studies, Research, Whitepapers

#### Search (Elasticsearch)
- Auto-complete khi gõ
- Tìm kiếm: Sản phẩm, Mã SKU, Tài liệu, Bài viết
- Bộ lọc kết quả theo loại nội dung, category, giá

### 2.2 Product Catalog

#### Product Listing
- Grid/List view toggle
- Pagination
- Sắp xếp: Mới nhất, Giá, Tên, Phổ biến
- Bộ lọc: Ứng dụng lâm sàng, Tính chất kỹ thuật, Trạng thái, Giá, Thương hiệu

#### Product Detail Page
- **Media Gallery:** Hình ảnh zoomable, Video
- **Thông tin:** Tên, SKU, Mô tả, Giá (hoặc "Liên hệ")
- **Specifications:** Bảng so sánh, Tabs (Overview, Specs, Applications, Features)
- **Downloads:** Brochures, Manuals, Data Sheets, Certificates
- **Related Products:** Phụ kiện, Sản phẩm tương tự
- **CTA:** Request Quote, Contact Sales, Schedule Demo

#### Request Quote Flow
1. User clicks "Request a Quote"
2. Form: Tên, Email, Phone, Company, Quantity, Message
3. Submit → Email to sales team (Phase 1: Gmail SMTP, Phase 2: SendGrid)
4. Confirmation message (Phase 1: Simple message, Phase 2: Email confirmation)

### 2.3 Content Management

#### Newsroom
- Danh sách bài viết với pagination
- Categories: Press Release, News, Announcements
- Filter theo ngày, category
- Article detail page với related articles
- Share buttons

#### Resource Center
- Types: Whitepapers, Videos, Research, Case Studies
- Filters: Specialty, Type, Year
- Download tracking
- Form đăng ký để download (lead gen)

### 2.4 Admin Panel (Filament)

- **Product Management:** CRUD, Upload images/videos, Specs, Documents, Bulk import/export
- **Content Management:** CRUD articles, resources, Media library
- **User Management:** Roles, permissions
- **Lead Management:** View và manage quote requests

### 2.5 Basic Service Request

- Form: Tên, Email, Phone, Company, Service Type, Description
- Email notification to service team (Phase 1: Gmail SMTP, Phase 2: SendGrid)
- Admin xem trong Filament

**Lưu ý:** Full tracking system sẽ có trong Phase 2.

---

## <a name="user-stories"></a>3. User Stories & Backlog (Phase 1)

### Epic 1: Navigation & Search

#### US-001: Mega Menu Navigation
**As a** người dùng  
**I want to** điều hướng dễ dàng qua các danh mục chính  
**So that** tôi có thể tìm thấy sản phẩm và thông tin nhanh chóng

**Acceptance Criteria:**
- [ ] Mega menu hiển thị khi hover/click
- [ ] Phân loại: Products, Services, Solutions, Education, Insights
- [ ] Sub-menu hiển thị các danh mục con
- [ ] Responsive trên mobile (hamburger menu)
- [ ] Breadcrumb navigation

**Priority:** High  
**Story Points:** 3

---

#### US-002: Tìm kiếm Nâng cao
**As a** bác sĩ hoặc người mua hàng  
**I want to** tìm kiếm sản phẩm và tài liệu với gợi ý tự động  
**So that** tôi có thể tìm thấy thông tin cần thiết nhanh chóng

**Acceptance Criteria:**
- [ ] Search bar với auto-complete
- [ ] Gợi ý khi gõ (sản phẩm, tài liệu, bài viết)
- [ ] Trang kết quả tìm kiếm với bộ lọc
- [ ] Highlight từ khóa trong kết quả
- [ ] Tìm kiếm theo mã sản phẩm (SKU)

**Priority:** High  
**Story Points:** 8

---

### Epic 2: Product Catalog

#### US-003: Xem Danh mục Sản phẩm
**As a** người mua hàng  
**I want to** xem danh sách sản phẩm với bộ lọc  
**So that** tôi có thể so sánh và tìm sản phẩm phù hợp

**Acceptance Criteria:**
- [ ] Grid/List view toggle
- [ ] Pagination
- [ ] Sắp xếp: Mới nhất, Giá, Tên, Phổ biến
- [ ] Bộ lọc sidebar (Ứng dụng, Tính chất kỹ thuật, Trạng thái, Giá, Thương hiệu)
- [ ] Hiển thị số lượng kết quả

**Priority:** High  
**Story Points:** 8

---

#### US-004: Xem Chi tiết Sản phẩm
**As a** bác sĩ hoặc kỹ thuật viên  
**I want to** xem thông tin chi tiết về một sản phẩm  
**So that** tôi có thể đánh giá và quyết định mua

**Acceptance Criteria:**
- [ ] Gallery hình ảnh với zoom
- [ ] Video giới thiệu (nếu có)
- [ ] Thông số kỹ thuật đầy đủ
- [ ] Bảng so sánh với model khác
- [ ] Tabs: Overview, Specs, Applications, Features
- [ ] Related products section

**Priority:** High  
**Story Points:** 5

---

#### US-005: Tải Tài liệu Kỹ thuật
**As a** bác sĩ  
**I want to** tải PDF kỹ thuật máy MRI để tham khảo  
**So that** tôi có thể nghiên cứu thông số trước khi quyết định

**Acceptance Criteria:**
- [ ] Section "Downloads" trên PDP
- [ ] Danh sách: Brochure, Manual, Data Sheet
- [ ] Click để download
- [ ] Tracking số lượt download (admin)

**Priority:** High  
**Story Points:** 3

---

#### US-006: Yêu cầu Báo giá
**As a** người mua hàng  
**I want to** gửi yêu cầu báo giá cho sản phẩm  
**So that** tôi có thể nhận được giá và thông tin từ sales team

**Acceptance Criteria:**
- [ ] Button "Request a Quote" trên PDP
- [ ] Modal/form với: Tên, Email, Phone, Company, Quantity, Message
- [ ] Validation form
- [ ] Email tự động gửi đến sales team (Phase 1: Gmail SMTP)
- [ ] Confirmation message (Phase 1: In-page message)
- [ ] Lưu vào database (leads table)

**Priority:** High  
**Story Points:** 5

---

### Epic 3: Content Management

#### US-007: Xem Tin tức & Thông cáo
**As a** người dùng quan tâm đến ngành y tế  
**I want to** đọc tin tức và thông cáo mới nhất  
**So that** tôi cập nhật thông tin về sản phẩm và công ty

**Acceptance Criteria:**
- [ ] Trang Newsroom với danh sách bài viết
- [ ] Filter theo: Press Release, News, Announcements
- [ ] Filter theo ngày
- [ ] Pagination
- [ ] Trang chi tiết bài viết
- [ ] Related articles
- [ ] Share buttons

**Priority:** Medium  
**Story Points:** 5

---

#### US-008: Tìm kiếm Tài liệu Chuyên môn
**As a** bác sĩ  
**I want to** tìm và tải whitepapers, case studies  
**So that** tôi có thể nghiên cứu và học hỏi

**Acceptance Criteria:**
- [ ] Trang Resource Center
- [ ] Bộ lọc theo: Specialty, Type, Year
- [ ] Grid/List view
- [ ] Trang chi tiết tài liệu
- [ ] Download button
- [ ] Form đăng ký để download (lead generation)

**Priority:** Medium  
**Story Points:** 5

---

### Epic 4: Admin Panel

#### US-009: Quản lý Sản phẩm (Admin)
**As an** admin  
**I want to** thêm, sửa, xóa sản phẩm  
**So that** tôi có thể cập nhật danh mục sản phẩm

**Acceptance Criteria:**
- [ ] CRUD sản phẩm trong Filament admin
- [ ] Upload multiple images
- [ ] Upload video
- [ ] Quản lý thông số kỹ thuật (dynamic fields)
- [ ] Upload tài liệu (PDF)
- [ ] Bulk import/export (CSV/Excel)

**Priority:** High  
**Story Points:** 8

---

#### US-010: Quản lý Nội dung (Admin)
**As an** admin  
**I want to** quản lý bài viết, tài liệu  
**So that** tôi có thể cập nhật nội dung website

**Acceptance Criteria:**
- [ ] CRUD bài viết (Newsroom)
- [ ] CRUD tài liệu (Resource Center)
- [ ] Rich text editor (WYSIWYG)
- [ ] Media library
- [ ] SEO fields (meta title, description)

**Priority:** High  
**Story Points:** 5

---

#### US-011: Quản lý Leads (Admin)
**As an** admin/sales  
**I want to** xem và quản lý các yêu cầu báo giá  
**So that** tôi có thể follow up với khách hàng

**Acceptance Criteria:**
- [ ] Danh sách leads/quotes requests
- [ ] Filter theo: Trạng thái, Ngày, Sản phẩm
- [ ] Xem chi tiết lead
- [ ] Đánh dấu trạng thái (New, Contacted, Converted, Lost)
- [ ] Export to CSV

**Priority:** High  
**Story Points:** 5

---

### Epic 5: Performance & SEO

#### US-012: Tối ưu Tốc độ Tải trang
**As a** người dùng  
**I want to** trang web tải nhanh  
**So that** tôi có trải nghiệm tốt

**Acceptance Criteria:**
- [ ] Page load time < 2 giây
- [ ] Lazy loading cho hình ảnh
- [ ] Image optimization (WebP format)
- [ ] CDN cho static assets
- [ ] Caching strategy (Redis)
- [ ] Database query optimization

**Priority:** High  
**Story Points:** 8

---

#### US-013: SEO Optimization
**As a** marketing team  
**I want to** website được tối ưu SEO  
**So that** khách hàng có thể tìm thấy chúng tôi trên Google

**Acceptance Criteria:**
- [ ] Schema markup cho sản phẩm (Product schema)
- [ ] Sitemap.xml tự động generate
- [ ] Robots.txt
- [ ] Meta tags (title, description) cho mỗi trang
- [ ] URL structure thân thiện
- [ ] Internal linking strategy

**Priority:** High  
**Story Points:** 5

---

### Epic 6: Security

#### US-014: Bảo mật Website
**As an** admin  
**I want to** website được bảo mật  
**So that** dữ liệu khách hàng được an toàn

**Acceptance Criteria:**
- [ ] SSL/TLS certificate
- [ ] Mã hóa dữ liệu nhạy cảm
- [ ] Rate limiting cho API
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] CSRF protection
- [ ] Security headers (CSP, HSTS)

**Priority:** High  
**Story Points:** 8

---

## Backlog Prioritization

### Sprint 1 (Foundation)
- US-001: Mega Menu Navigation
- US-003: Xem Danh mục Sản phẩm
- US-004: Xem Chi tiết Sản phẩm
- US-009: Quản lý Sản phẩm (Admin)

### Sprint 2 (Core Features)
- US-002: Tìm kiếm Nâng cao
- US-005: Tải Tài liệu Kỹ thuật
- US-006: Yêu cầu Báo giá
- US-010: Quản lý Nội dung (Admin)
- US-011: Quản lý Leads (Admin)

### Sprint 3 (Content & Polish)
- US-007: Xem Tin tức & Thông cáo
- US-008: Tìm kiếm Tài liệu Chuyên môn
- US-012: Tối ưu Tốc độ
- US-013: SEO Optimization

### Sprint 4 (Security)
- US-014: Bảo mật Website

---

## Definition of Done

Mỗi user story được coi là "Done" khi:
- [ ] Code được review và approved
- [ ] Unit tests đã viết và pass
- [ ] Manual testing đã hoàn thành
- [ ] Responsive design đã test trên mobile/tablet/desktop
- [ ] Documentation đã cập nhật (nếu cần)
- [ ] Deployed lên staging environment
- [ ] Product Owner đã accept

---

## Notes

- Story Points sử dụng Fibonacci scale (1, 2, 3, 5, 8, 13)
- Priority: High, Medium, Low
- Phase 2 features sẽ được thêm vào backlog sau khi Phase 1 hoàn thành


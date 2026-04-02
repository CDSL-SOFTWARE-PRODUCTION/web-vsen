# User Journey Diagrams - VSEN Medical Equipment Website

## 1. Visitor Journey - Tìm kiếm và Yêu cầu Báo giá

```mermaid
journey
    title Visitor Journey: Tìm kiếm Sản phẩm và Yêu cầu Báo giá
    section Khám phá
      Truy cập website: 5: Visitor
      Xem trang chủ: 4: Visitor
      Tìm kiếm sản phẩm: 5: Visitor
      Xem kết quả tìm kiếm: 4: Visitor
    section Xem chi tiết
      Click vào sản phẩm: 5: Visitor
      Xem hình ảnh và thông số: 4: Visitor
      Download tài liệu PDF: 5: Visitor
      Xem sản phẩm liên quan: 3: Visitor
    section Quyết định
      So sánh sản phẩm: 4: Visitor
      Đọc tin tức liên quan: 3: Visitor
      Quyết định quan tâm: 5: Visitor
    section Yêu cầu báo giá
      Click "Request Quote": 4: Visitor
      Điền thông tin form: 3: Visitor
      Gửi yêu cầu: 5: Visitor
      Nhận xác nhận: 5: Visitor
```

## 2. Visitor Journey - Đọc Tin tức và Tài liệu

```mermaid
journey
    title Visitor Journey: Đọc Tin tức và Tài liệu Chuyên môn
    section Khám phá
      Truy cập website: 5: Visitor
      Vào mục Insights/News: 4: Visitor
      Xem danh sách bài viết: 4: Visitor
    section Đọc nội dung
      Chọn bài viết quan tâm: 5: Visitor
      Đọc nội dung: 5: Visitor
      Xem hình ảnh minh họa: 4: Visitor
    section Tải tài liệu
      Vào mục Resources: 4: Visitor
      Tìm tài liệu cần thiết: 5: Visitor
      Download tài liệu PDF: 5: Visitor
    section Liên hệ
      Tìm thông tin liên hệ: 3: Visitor
      Gửi câu hỏi qua Contact Form: 4: Visitor
```

## 3. Admin Journey - Quản lý Sản phẩm

```mermaid
journey
    title Admin Journey: Quản lý Sản phẩm
    section Đăng nhập
      Truy cập Admin Panel: 5: Admin
      Đăng nhập: 5: Admin
      Vào mục Products: 4: Admin
    section Tạo sản phẩm
      Click "Create Product": 4: Admin
      Điền thông tin cơ bản: 3: Admin
      Upload hình ảnh: 4: Admin
      Thêm thông số kỹ thuật: 3: Admin
      Upload tài liệu PDF: 4: Admin
      Lưu sản phẩm: 5: Admin
    section Quản lý
      Xem danh sách sản phẩm: 5: Admin
      Sửa sản phẩm: 4: Admin
      Xóa sản phẩm: 3: Admin
      Cập nhật trạng thái: 4: Admin
```

## 4. Admin Journey - Xử lý Leads

```mermaid
journey
    title Admin Journey: Xử lý Yêu cầu Báo giá (Leads)
    section Nhận thông báo
      Nhận email về lead mới: 5: Admin
      Mở email xem chi tiết: 4: Admin
    section Xem và đánh giá
      Truy cập Admin Panel: 4: Admin
      Vào mục Quote Requests: 5: Admin
      Xem danh sách leads: 5: Admin
      Lọc theo trạng thái: 4: Admin
      Xem chi tiết lead: 5: Admin
    section Xử lý
      Đánh giá lead: 4: Admin
      Cập nhật trạng thái: "Contacted": 4: Admin
      Liên hệ khách hàng: 5: Admin
      Cập nhật kết quả: "Converted" hoặc "Lost": 5: Admin
```


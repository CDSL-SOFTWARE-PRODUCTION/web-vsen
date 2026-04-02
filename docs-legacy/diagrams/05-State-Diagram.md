# State Diagram - VSEN Medical Equipment Website

## 1. Quote Request Status State Machine

```mermaid
stateDiagram-v2
    [*] --> new: Visitor submits quote request
    
    new --> contacted: Admin contacts customer
    new --> spam: Mark as spam/invalid
    
    contacted --> converted: Customer converts to sale
    contacted --> lost: Customer not interested
    contacted --> new: Re-open as new
    
    converted --> [*]: Final state
    lost --> [*]: Final state
    spam --> [*]: Final state
    
    note right of new
        Initial state
        Sales team chưa liên hệ
    end note
    
    note right of contacted
        Admin đã liên hệ
        Đang follow up
    end note
    
    note right of converted
        Khách hàng đã mua
        Thành công
    end note
```

## 2. Article Publishing State Machine

```mermaid
stateDiagram-v2
    [*] --> draft: Admin creates article
    
    draft --> published: Admin publishes
    draft --> [*]: Admin deletes
    
    published --> draft: Admin unpublishes
    published --> [*]: Admin deletes
    
    note right of draft
        Article chưa hiển thị
        Chỉ admin thấy
    end note
    
    note right of published
        Article đã public
        Visitor có thể xem
    end note
```

## 3. Product Status State Machine

```mermaid
stateDiagram-v2
    [*] --> new: Admin creates product
    
    new --> active: Admin activates
    new --> inactive: Admin deactivates
    new --> [*]: Admin deletes
    
    active --> inactive: Admin deactivates
    active --> [*]: Admin deletes
    
    inactive --> active: Admin reactivates
    inactive --> [*]: Admin deletes
    
    note right of new
        Product mới tạo
        Chưa hiển thị
    end note
    
    note right of active
        Product đang bán
        Hiển thị cho Visitor
    end note
    
    note right of inactive
        Product tạm ngưng
        Không hiển thị
    end note
```


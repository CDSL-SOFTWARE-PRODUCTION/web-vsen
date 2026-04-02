# Entity Relationship Diagram (ERD) - VSEN Medical Equipment Website

## Database Schema Overview

### Core Entities

1. **categories** - Product categories (hierarchical)
2. **products** - Main product entity
3. **product_images** - Product images
4. **product_specs** - Product specifications
5. **product_documents** - Product documents (PDFs, manuals)
6. **product_relations** - Related products (many-to-many)
7. **quote_requests** - Quote requests from visitors
8. **articles** - News articles
9. **resources** - Resources (whitepapers, case studies)
10. **contacts** - Contact form submissions
11. **users** - Admin users

## Entity Relationship Diagram (Mermaid)

```mermaid
erDiagram
    CATEGORIES ||--o{ PRODUCTS : "has"
    PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
    PRODUCTS ||--o{ PRODUCT_SPECS : "has"
    PRODUCTS ||--o{ PRODUCT_DOCUMENTS : "has"
    PRODUCTS ||--o{ QUOTE_REQUESTS : "generates"
    PRODUCTS }o--o{ PRODUCTS : "related_to"
    
    CATEGORIES {
        int id PK
        int parent_id FK
        string slug UK
        string name
        text description
        string image
        int sort_order
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    PRODUCTS {
        int id PK
        string sku UK
        string slug UK
        string name
        text short_description
        text description
        int category_id FK
        decimal price
        boolean show_price
        string status
        string brand
        string model
        string video_url
        string meta_title
        text meta_description
        boolean is_featured
        boolean is_active
        int view_count
        timestamp created_at
        timestamp updated_at
    }
    
    PRODUCT_IMAGES {
        int id PK
        int product_id FK
        string path
        string url
        string alt_text
        int sort_order
        boolean is_primary
        timestamp created_at
    }
    
    PRODUCT_SPECS {
        int id PK
        int product_id FK
        string spec_key
        text spec_value
        string spec_group
        int sort_order
        timestamp created_at
    }
    
    PRODUCT_DOCUMENTS {
        int id PK
        int product_id FK
        string name
        string file_path
        string file_url
        string file_type
        int file_size
        int download_count
        int sort_order
        timestamp created_at
    }
    
    PRODUCT_RELATIONS {
        int product_id FK
        int related_product_id FK
        timestamp created_at
    }
    
    QUOTE_REQUESTS {
        int id PK
        int product_id FK
        string name
        string email
        string phone
        string company
        int quantity
        text message
        string status
        timestamp created_at
        timestamp updated_at
    }
    
    ARTICLES {
        int id PK
        string slug UK
        string title
        text excerpt
        text content
        string featured_image
        string category
        int view_count
        boolean is_published
        timestamp published_at
        string meta_title
        text meta_description
        timestamp created_at
        timestamp updated_at
    }
    
    RESOURCES {
        int id PK
        string slug UK
        string title
        text description
        string resource_type
        string file_path
        string file_url
        string thumbnail
        string specialty
        int publish_year
        int download_count
        boolean is_public
        timestamp created_at
        timestamp updated_at
    }
    
    CONTACTS {
        int id PK
        string name
        string email
        string phone
        string company
        string subject
        text message
        string status
        timestamp created_at
    }
    
    USERS {
        int id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string role
        timestamp created_at
        timestamp updated_at
    }
```

## Relationships Detail

### 1. Categories → Products (One-to-Many)
- One category can have many products
- Product belongs to one category (nullable)
- Foreign Key: `products.category_id` → `categories.id`
- ON DELETE: SET NULL (allows category deletion)

### 2. Products → Product Images (One-to-Many)
- One product can have many images
- Foreign Key: `product_images.product_id` → `products.id`
- ON DELETE: CASCADE (images deleted when product deleted)
- Constraint: Only one primary image per product

### 3. Products → Product Specs (One-to-Many)
- One product can have many specifications
- Foreign Key: `product_specs.product_id` → `products.id`
- ON DELETE: CASCADE

### 4. Products → Product Documents (One-to-Many)
- One product can have many documents
- Foreign Key: `product_documents.product_id` → `products.id`
- ON DELETE: CASCADE

### 5. Products → Quote Requests (One-to-Many)
- One product can generate many quote requests
- Foreign Key: `quote_requests.product_id` → `products.id`
- ON DELETE: SET NULL (allows product deletion)

### 6. Products ↔ Products (Many-to-Many)
- Products can be related to other products
- Junction table: `product_relations`
- Foreign Keys: `product_id`, `related_product_id` → `products.id`
- ON DELETE: CASCADE

### 7. Categories → Categories (Self-Referential)
- Categories can have parent categories (hierarchical)
- Foreign Key: `categories.parent_id` → `categories.id`
- ON DELETE: SET NULL

## Key Constraints

### Unique Constraints
- `products.sku` - Unique SKU per product
- `products.slug` - Unique slug per product
- `categories.slug` - Unique slug per category
- `articles.slug` - Unique slug per article
- `resources.slug` - Unique slug per resource
- `users.email` - Unique email per user

### Indexes

**Performance Indexes:**
- `products.category_id` - For filtering by category
- `products.is_active` - For active products filter
- `products.status` - For status filter
- `products.brand` - For brand filter
- `products.sku` - For SKU search
- `quote_requests.status` - For lead management
- `quote_requests.created_at` - For date sorting
- `articles.is_published` + `articles.published_at` - Composite index for published articles

**Partial Unique Index:**
- `product_images.is_primary` - Ensures only one primary image per product

## Data Flow Summary

```
Visitor Request Quote
    ↓
quote_requests table
    ↓
Admin views in Filament
    ↓
Sales team follows up
```

```
Admin creates Product
    ↓
products table
    ↓
product_images, product_specs, product_documents
    ↓
Visitor views Product Detail
```


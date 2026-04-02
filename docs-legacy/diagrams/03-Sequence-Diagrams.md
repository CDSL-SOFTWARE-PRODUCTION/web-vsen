# Sequence Diagrams - VSEN Medical Equipment Website

## Overview

Sequence diagrams mô tả luồng tương tác giữa các components trong các use cases chính.

## 1. Search Products Sequence

```mermaid
sequenceDiagram
    participant V as Visitor
    participant F as Frontend (React)
    participant API as Laravel API
    participant SC as SearchController
    participant SS as SearchService
    participant ES as Elasticsearch
    participant DB as PostgreSQL
    participant PC as ProductController
    
    V->>F: Enter search query
    F->>API: GET /search/autocomplete?q=...
    API->>SC: autocomplete()
    SC->>SS: autocomplete(query)
    SS->>ES: Search suggestions
    ES-->>SS: Return suggestions
    SS-->>SC: Format results
    SC-->>API: JSON response
    API-->>F: Display suggestions
    
    V->>F: Select suggestion or submit
    F->>API: GET /search?q=...&type=product
    API->>SC: search()
    SC->>SS: search(query, filters)
    SS->>ES: Full-text search
    ES-->>SS: Return product IDs
    SS->>DB: Get product details by IDs
    DB-->>SS: Product data
    SS-->>SC: Formatted results
    SC-->>API: JSON response
    API-->>F: Display search results
    F-->>V: Show products
```

## 2. View Product Detail Sequence

```mermaid
sequenceDiagram
    participant V as Visitor
    participant F as Frontend (React)
    participant API as Laravel API
    participant PC as ProductController
    participant P as Product Model
    participant DB as PostgreSQL
    participant Cache as Redis
    
    V->>F: Click on product
    F->>API: GET /products/{slug}
    API->>Cache: Check cache
    alt Cache Hit
        Cache-->>API: Return cached data
    else Cache Miss
        API->>PC: show(slug)
        PC->>P: findBySlug(slug)
        P->>DB: Load product with relations
        DB-->>P: Product + images + specs + documents
        P->>P: Load related products
        P->>DB: Query related products
        DB-->>P: Related products
        P-->>PC: Product object
        PC->>Cache: Store in cache
        PC-->>API: JSON response
    end
    API-->>F: Product data
    F->>F: Render ProductDetail page
    F-->>V: Display product information
    
    V->>F: Click download document
    F->>API: GET /products/{id}/documents/{docId}/download
    API->>PC: downloadDocument(id, docId)
    PC->>P: Find document
    P->>DB: Get document
    DB-->>P: Document info
    P->>P: Increment download_count
    P->>DB: Update count
    PC->>PC: Return file
    PC-->>API: File download
    API-->>F: Download file
    F-->>V: File downloaded
```

## 3. Request Quote Sequence

```mermaid
sequenceDiagram
    participant V as Visitor
    participant F as Frontend (React)
    participant API as Laravel API
    participant QRC as QuoteRequestController
    participant QR as QuoteRequest Model
    participant ES as EmailService
    participant Mail as Mail Queue
    participant DB as PostgreSQL
    
    V->>F: Click "Request Quote" button
    F->>F: Show quote request form
    V->>F: Fill form (name, email, phone, company, quantity, message)
    V->>F: Submit form
    F->>API: POST /quote-requests
    API->>QRC: store(QuoteRequestRequest)
    QRC->>QRC: Validate request
    alt Validation fails
        QRC-->>API: Validation errors
        API-->>F: 422 Unprocessable Entity
        F-->>V: Show errors
    else Validation passes
        QRC->>QR: create(data)
        QR->>DB: Insert quote request
        DB-->>QR: Created quote request
        QR-->>QRC: QuoteRequest object
        QRC->>ES: sendQuoteRequestNotification(quoteRequest)
        ES->>Mail: Queue email to sales team
        Mail-->>ES: Email queued
        ES-->>QRC: Email sent
        QRC-->>API: Success response
        API-->>F: 201 Created
        F-->>V: Show success message
    end
```

## 4. Admin Create Product Sequence

```mermaid
sequenceDiagram
    participant A as Admin
    participant F as Filament Admin Panel
    participant API as Laravel API
    participant PC as ProductController
    participant P as Product Model
    participant SS as SearchService
    participant ES as Elasticsearch
    participant FS as FileStorage
    participant DB as PostgreSQL
    
    A->>F: Navigate to Products > Create
    F->>F: Display create form
    A->>F: Fill product information
    A->>F: Upload images
    A->>F: Add specifications
    A->>F: Upload documents
    A->>F: Submit form
    F->>API: POST /admin/products
    API->>PC: store(ProductRequest)
    PC->>PC: Validate request
    
    PC->>FS: Store images
    FS-->>PC: Image paths
    PC->>FS: Store documents
    FS-->>PC: Document paths
    
    PC->>P: create(productData)
    P->>DB: Begin transaction
    P->>DB: Insert product
    DB-->>P: Product ID
    P->>P: Attach images
    P->>DB: Insert product_images
    P->>P: Attach specs
    P->>DB: Insert product_specs
    P->>P: Attach documents
    P->>DB: Insert product_documents
    P->>DB: Commit transaction
    DB-->>P: Success
    P-->>PC: Product object
    
    PC->>SS: indexProduct(product)
    SS->>ES: Index product in Elasticsearch
    ES-->>SS: Indexed
    SS-->>PC: Success
    
    PC-->>API: 201 Created
    API-->>F: Success response
    F-->>A: Show success message
```

## 5. View Articles Sequence

```mermaid
sequenceDiagram
    participant V as Visitor
    participant F as Frontend (React)
    participant API as Laravel API
    participant CC as ContentController
    participant A as Article Model
    participant DB as PostgreSQL
    participant Cache as Redis
    
    V->>F: Navigate to /insights/news
    F->>API: GET /articles?page=1&category=news
    API->>Cache: Check cache key "articles:page:1:category:news"
    alt Cache Hit
        Cache-->>API: Return cached articles
    else Cache Miss
        API->>CC: articles(Request)
        CC->>A: published()->byCategory()->paginate()
        A->>DB: Query published articles
        DB-->>A: Articles data
        A-->>CC: Paginated collection
        CC->>Cache: Store in cache (5 minutes)
        CC-->>API: JSON response
    end
    API-->>F: Articles list
    F->>F: Render ArticlesList page
    F-->>V: Display articles
    
    V->>F: Click on article
    F->>API: GET /articles/{slug}
    API->>CC: article(slug)
    CC->>A: findBySlug(slug)
    A->>DB: Load article
    DB-->>A: Article data
    A->>A: Increment view_count
    A->>DB: Update view_count
    A-->>CC: Article object
    CC->>A: loadRelated()
    A->>DB: Query related articles
    DB-->>A: Related articles
    CC-->>API: JSON response
    API-->>F: Article detail
    F-->>V: Display article
```

## 6. Download Resource Sequence

```mermaid
sequenceDiagram
    participant V as Visitor
    participant F as Frontend (React)
    participant API as Laravel API
    participant CC as ContentController
    participant R as Resource Model
    participant DB as PostgreSQL
    participant FS as FileStorage
    
    V->>F: Navigate to /resources
    F->>API: GET /resources
    API->>CC: resources(Request)
    CC->>R: public()->paginate()
    R->>DB: Query public resources
    DB-->>R: Resources data
    R-->>CC: Paginated collection
    CC-->>API: JSON response
    API-->>F: Resources list
    F-->>V: Display resources
    
    V->>F: Click download resource
    F->>API: GET /resources/{id}/download
    API->>CC: download(id)
    CC->>R: find(id)
    R->>DB: Get resource
    DB-->>R: Resource info
    R->>R: Increment download_count
    R->>DB: Update count
    R-->>CC: Resource object
    CC->>FS: Get file from storage
    FS-->>CC: File stream
    CC-->>API: File download response
    API-->>F: Download file
    F-->>V: File downloaded
```

## Key Interactions

### Caching Strategy
- **Product listings:** Cache 5 minutes
- **Product detail:** Cache 10 minutes
- **Articles:** Cache 5 minutes
- **Search results:** Cache 2 minutes

### Database Transactions
- Product creation uses transaction to ensure data consistency
- All related data (images, specs, documents) created atomically

### Queue Processing
- Email notifications queued for async processing
- Prevents blocking user requests


# Technical Documentation
## Website Bán Thiết Bị Y Tế - VSEN Medical Equipment

**Version:** 1.0  
**Date:** 2025-12-28

---

## Mục lục

1. [System Architecture](#architecture)
2. [Database Schema](#database)
3. [API Documentation](#api)
4. [Infrastructure & Deployment](#infrastructure)

---

## <a name="architecture"></a>1. System Architecture

### 1.1 Technology Stack

**Backend:**
- Laravel 11.x
- PHP 8.2+
- PostgreSQL 15+
- Redis 7+ (Cache)
- Elasticsearch 8.x hoặc Meilisearch (Search)

**Frontend:**
- React 18+ (Laravel React Starter Kit)
- Vite
- Tailwind CSS

**Admin:**
- Filament 3.x
- Breezy (Authentication - Phase 2)

### 1.2 Architecture Overview

#### Phase 1 (MVP) - Single VPS Architecture

```
CDN (CloudFlare - Optional)
    │
    ▼
Single VPS (8GB RAM, 4 vCPU)
    │
    ├──► Nginx (Web Server)
    ├──► Laravel Application
    ├──► PostgreSQL Database
    ├──► Redis (Cache)
    └──► File Storage (Local Disk)
```

**Lưu ý:** Tất cả services chạy trên 1 VPS duy nhất cho Phase 1 (MVP).

#### Phase 2 (Scale) - Separated Services Architecture

```
CDN (CloudFlare)
    │
    ▼
Load Balancer (Nginx)
    │
    ├──► Web Server 1 (Laravel)
    └──► Web Server 2 (Laravel)
            │
            ├──► PostgreSQL Database (Separate Server)
            ├──► Redis (Cache - Separate Service)
            ├──► Elasticsearch (Search - Separate Service)
            └──► S3/Spaces (File Storage)
```

### 1.3 Folder Structure

```
app/
├── Http/Controllers/Api/
│   ├── ProductController.php
│   ├── SearchController.php
│   ├── QuoteRequestController.php
│   └── ContentController.php
├── Models/
│   ├── Product.php
│   ├── Category.php
│   ├── Article.php
│   ├── Resource.php
│   └── QuoteRequest.php
└── Services/
    ├── SearchService.php
    └── EmailService.php

resources/js/
├── Components/
│   ├── Product/
│   ├── Search/
│   └── Common/
├── Pages/
│   ├── Products/
│   └── News/
└── App.tsx
```

---

## <a name="database"></a>2. Database Schema (Phase 1)

### 2.1 Core Tables

#### `categories`
```sql
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    parent_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_parent ON categories(parent_id);
CREATE INDEX idx_categories_active ON categories(is_active);
```

#### `products`
```sql
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    sku VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_description TEXT,
    description TEXT,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    price DECIMAL(15, 2) NULL,
    show_price BOOLEAN DEFAULT false,
    status VARCHAR(50) DEFAULT 'new',
    brand VARCHAR(100),
    model VARCHAR(100),
    video_url VARCHAR(500),
    meta_title VARCHAR(255),
    meta_description TEXT,
    is_featured BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    view_count INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_products_brand ON products(brand);
CREATE INDEX idx_products_sku ON products(sku);

#### `product_relations`
```sql
CREATE TABLE product_relations (
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    related_product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, related_product_id)
);

CREATE INDEX idx_product_relations_product ON product_relations(product_id);
```
```

#### `product_images`
```sql
CREATE TABLE product_images (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    path VARCHAR(255) NOT NULL,
    url VARCHAR(500),
    alt_text VARCHAR(255),
    sort_order INTEGER DEFAULT 0,
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_product_images_product ON product_images(product_id);
CREATE INDEX idx_product_images_primary ON product_images(product_id, is_primary) WHERE is_primary = true;

-- Constraint: Only one primary image per product
CREATE UNIQUE INDEX idx_product_images_one_primary ON product_images(product_id) WHERE is_primary = true;
```

#### `product_specs`
```sql
CREATE TABLE product_specs (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    spec_key VARCHAR(100) NOT NULL,
    spec_value TEXT NOT NULL,
    spec_group VARCHAR(50),
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_product_specs_product ON product_specs(product_id);
CREATE INDEX idx_product_specs_group ON product_specs(spec_group);
```

#### `product_documents`
```sql
CREATE TABLE product_documents (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_url VARCHAR(500),
    file_type VARCHAR(50),
    file_size INTEGER,
    download_count INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_product_documents_product ON product_documents(product_id);
CREATE INDEX idx_product_documents_type ON product_documents(file_type);
```

#### `quote_requests`
```sql
CREATE TABLE quote_requests (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    quantity INTEGER DEFAULT 1,
    message TEXT,
    status VARCHAR(50) DEFAULT 'new',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_quote_requests_product ON quote_requests(product_id);
CREATE INDEX idx_quote_requests_status ON quote_requests(status);
CREATE INDEX idx_quote_requests_email ON quote_requests(email);
CREATE INDEX idx_quote_requests_created ON quote_requests(created_at);
```

#### `articles`
```sql
CREATE TABLE articles (
    id SERIAL PRIMARY KEY,
    slug VARCHAR(255) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content TEXT NOT NULL,
    featured_image VARCHAR(255),
    category VARCHAR(50),
    view_count INTEGER DEFAULT 0,
    is_published BOOLEAN DEFAULT false,
    published_at TIMESTAMPTZ NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_articles_published ON articles(is_published, published_at);
CREATE INDEX idx_articles_category ON articles(category);
CREATE INDEX idx_articles_created ON articles(created_at);
```

#### `resources`
```sql
CREATE TABLE resources (
    id SERIAL PRIMARY KEY,
    slug VARCHAR(255) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    resource_type VARCHAR(50),
    file_path VARCHAR(255),
    file_url VARCHAR(500),
    thumbnail VARCHAR(255),
    specialty VARCHAR(100),
    publish_year INTEGER,
    download_count INTEGER DEFAULT 0,
    is_public BOOLEAN DEFAULT true,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_resources_type ON resources(resource_type);
CREATE INDEX idx_resources_specialty ON resources(specialty);
CREATE INDEX idx_resources_year ON resources(publish_year);
CREATE INDEX idx_resources_public ON resources(is_public);
```

#### `contacts`
```sql
CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'new',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_contacts_status ON contacts(status);
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_created ON contacts(created_at);
```

#### `users` (Admin only - Phase 1)
```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMPTZ NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_verified ON users(email_verified_at);
```

### 2.2 Relationships

- `Category` → `Products` (1-n)
- `Product` → `ProductImages` (1-n)
- `Product` → `ProductSpecs` (1-n)
- `Product` → `ProductDocuments` (1-n)
- `Product` → `QuoteRequests` (1-n)

### 2.3 Database Optimizations

#### Indexes Strategy

**Foreign Key Indexes:**
- Tất cả foreign keys đều có indexes để tối ưu JOIN queries
- Ví dụ: `product_id` trong `product_images`, `product_specs`, `product_documents`

**Filtering & Sorting Indexes:**
- Indexes cho các columns thường dùng để filter: `status`, `is_active`, `is_published`, `brand`, `category`
- Indexes cho sorting: `created_at`, `published_at`
- Composite indexes cho queries phổ biến: `(is_published, published_at)` cho articles

**Search Indexes:**
- `sku` index cho SKU search (US-002)
- `email` indexes cho lead management (US-011)
- `slug` indexes (đã có từ UNIQUE constraint)

**Special Constraints:**
- Partial unique index cho `product_images.is_primary` - đảm bảo chỉ 1 primary image per product

#### Data Types

- **TIMESTAMPTZ**: Sử dụng timezone-aware timestamps thay vì TIMESTAMP để hỗ trợ multi-timezone users
- **ON DELETE Actions**: 
  - `SET NULL` cho `products.category_id` và `quote_requests.product_id` (cho phép xóa parent)
  - `CASCADE` cho related data (images, specs, documents sẽ tự động xóa khi product bị xóa)

#### Performance Impact

Với các indexes này:
- **Query performance**: Tăng tốc 10-100x cho các queries filter/sort/search
- **JOIN operations**: Tối ưu khi load related data (product với images, specs, etc.)
- **Scalability**: Database có thể handle 100,000+ products mà vẫn maintain performance

---

## <a name="api"></a>3. API Documentation

### 3.1 Base URL
`https://api.vsen.com/api/v1`

### 3.2 Response Format

**Success:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error message"
  }
}
```

### 3.3 Public APIs (Phase 1)

#### Products

**GET /products**
- Query: `page`, `per_page`, `category_id`, `status`, `search`, `sort`, `order`
- Response: List of products with pagination

**GET /products/{slug}**
- Response: Product detail with images, specs, documents, related products

**GET /categories**
- Response: Category tree

#### Search

**GET /search**
- Query: `q` (required), `type`, `category_id`, `page`
- Response: Search results (products, articles, resources)

**GET /search/autocomplete**
- Query: `q` (required, min 2 chars)
- Response: Suggestions array

#### Content

**GET /articles**
- Query: `page`, `category`, `search`, `sort`
- Response: List of articles

**GET /articles/{slug}**
- Response: Article detail

**GET /resources**
- Query: `page`, `type`, `specialty`, `year`
- Response: List of resources

#### Contact & Leads

**POST /quote-requests**
- Body: `product_id`, `name`, `email`, `phone`, `company`, `quantity`, `message`
- Response: Success message

**POST /contacts**
- Body: `name`, `email`, `phone`, `company`, `subject`, `message`
- Response: Success message

### 3.4 Admin APIs (Filament)

- Product CRUD: `/admin/products`
- Article CRUD: `/admin/articles`
- Resource CRUD: `/admin/resources`
- Quote Requests: `/admin/quote-requests`

---

## <a name="infrastructure"></a>4. Infrastructure & Deployment

### 4.1 Production Setup

#### Phase 1 (MVP) - Single VPS Setup (Recommended)

**Single VPS (8GB RAM, 4 vCPU, 200GB SSD):**
- DigitalOcean Droplet: $48/month
- Vultr: $48/month
- Linode: $48/month
- Hetzner: €41/month (~$45/month)

**Tất cả services chạy trên 1 VPS:**
- Laravel + Nginx (Web Server)
- PostgreSQL Database
- Redis (Cache)
- Elasticsearch (Search)
- File Storage (local disk)
- Email: Gmail SMTP (free)

**Total Phase 1:** ~$45-48/month

**Lưu ý:**
- ✅ **Recommended cho MVP** với traffic thấp (< 10,000 visits/month)
- ✅ Tất cả services trên 1 server (đơn giản, dễ quản lý)
- ✅ File storage sử dụng VPS disk space (cần setup automatic backup)
- ✅ Email sử dụng Gmail SMTP (đủ cho MVP, < 50 requests/ngày)
- ✅ Cost-effective cho startup/MVP phase

---

**Alternative Options (Cho reference):**

**Option 2: Separated Services (Cho scale lớn hơn)**

**Servers:**
- 2x Web Servers (4GB RAM, 2 vCPU) - $48-96/month
- PostgreSQL Database (4GB RAM, 100GB) - $60-120/month
- Redis (1GB) - $15-30/month
- Elasticsearch (2GB) - $12-24/month
- File Storage: VPS storage (included với Web Servers) - $0
- Email: Gmail SMTP (free) - $0

**Total Option 2:** ~$135-270/month

**Lưu ý:**
- Phù hợp khi cần scale, high availability
- Services tách biệt (dễ scale từng phần)
- Cần load balancer cho multiple web servers

---


#### Phase 2 (Scale)

**Base (giữ nguyên Single VPS):**
- Single VPS: $48/month (hoặc upgrade lên 16GB RAM nếu cần)

**Additional Services:**
- File Storage: S3/Spaces (100GB) - $5-15/month (migrate từ VPS)
- Email: SendGrid/Mailgun (10,000 emails/month) - $15/month

**Total Phase 2:** ~$68-78/month

**Lưu ý:**
- Có thể giữ Single VPS nếu traffic vẫn < 50,000 visits/month
- Chỉ migrate file storage và email sang external services
- Khi cần scale lớn hơn → chuyển sang Option 2 (Separated Services)

### 4.2 Deployment

**CI/CD:** GitHub Actions
- Run tests
- Build assets
- Deploy to staging (auto)
- Deploy to production (manual approval)

**Process:**
1. Code push → Trigger CI
2. Tests → Build → Deploy staging
3. Manual testing
4. Deploy production (blue-green)

### 4.3 Environment Variables

#### Phase 1 (MVP)

```env
APP_NAME="VSEN Medical"
APP_ENV=production
APP_URL=https://vsen.com

DB_CONNECTION=pgsql
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

REDIS_HOST=
REDIS_PORT=6379

ELASTICSEARCH_HOST=
ELASTICSEARCH_PORT=9200

# Email: Gmail SMTP (Phase 1)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@vsen.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage: VPS (Phase 1)
FILESYSTEM_DISK=local
# Hoặc public disk
# FILESYSTEM_DISK=public
```

**Lưu ý Gmail SMTP:**
- Cần tạo App Password từ Google Account (không dùng password thường)
- Giới hạn: 500 emails/ngày (đủ cho MVP)
- Alternative: Có thể dùng mailto link cho contact forms

#### Phase 2 (Scale)

```env
# Email: SendGrid/Mailgun (Phase 2)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_FROM_ADDRESS=noreply@vsen.com

# File Storage: S3/Spaces (Phase 2)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_ENDPOINT=  # Cho DigitalOcean Spaces
```

### 4.4 Monitoring

- **Error Tracking:** Sentry (free tier)
- **Uptime:** UptimeRobot (free)
- **Analytics:** Google Analytics

### 4.5 Backup

#### Phase 1 (MVP)

- **Database:** Daily automated backups (30-day retention)
- **Files:** Daily automated backup to VPS backup directory
  ```bash
  # Cron job example (chạy hàng ngày lúc 2h sáng)
  0 2 * * * tar -czf /backup/files-$(date +\%Y\%m\%d).tar.gz /var/www/storage/app/public
  ```
- **Location:** VPS backup directory (có thể sync lên external storage)
- **Monitoring:** Alert khi disk space > 80%

#### Phase 2 (Scale)

- **Database:** Daily automated backups (30-day retention)
- **Files:** Migrate sang S3/Spaces với automatic backup
- **Location:** S3/Spaces with encryption + versioning

---

## Quick Start

### Development Setup

```bash
# Clone repository
git clone [repo-url]
cd web-vsen

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate
php artisan db:seed

# Start development
php artisan serve
npm run dev
```

### Production Deployment

```bash
# On server
git pull origin main
composer install --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Notes

- Phase 1 focuses on core features only
- No multi-language/country in Phase 1
- No Events/Webinars in Phase 1
- No user authentication in Phase 1 (admin only)
- All features marked for Phase 2 will be added later

## Infrastructure Migration Plan (Phase 1 → Phase 2)

### Email Migration

**Phase 1:** Gmail SMTP
- Setup: Configure Laravel Mail với Gmail SMTP
- Limit: 500 emails/ngày
- Cost: Free

**Phase 2:** SendGrid/Mailgun
- Migration: Update `.env` file với SendGrid credentials
- Benefits: Automation, tracking, better deliverability
- Cost: ~$15/month (10,000 emails)

### File Storage Migration

**Phase 1:** VPS Storage (Recommended)
- Setup: Sử dụng `local` hoặc `public` disk trên Single VPS
- Backup: Daily cron job backup files
- Monitoring: Disk space alerts
- Cost: Included với VPS ($0 additional)
- **Lưu ý:** Đủ cho MVP với < 10,000 products và < 50GB files

**Phase 2:** S3/Spaces (Optional - chỉ khi cần)
- **Khi nào cần migrate:**
  - Disk space > 80% của VPS
  - Traffic > 50,000 visits/month
  - Cần CDN cho better performance
- Migration steps:
  1. Setup S3/Spaces bucket
  2. Install AWS SDK: `composer require league/flysystem-aws-s3-v3`
  3. Update `config/filesystems.php`
  4. Migrate existing files: `php artisan storage:link` + sync script
  5. Update code to use S3 disk
- Benefits: CDN, auto-scaling, better performance
- Cost: ~$5-15/month (100GB)
- **Alternative:** Có thể upgrade VPS disk space thay vì migrate sang S3

**Migration Script Example:**
```bash
# Backup existing files
tar -czf backup-files.tar.gz storage/app/public

# Sync to S3 (sử dụng AWS CLI hoặc Laravel command)
php artisan migrate:files-to-s3
```


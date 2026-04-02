# Design Documentation

## Website Bán Thiết Bị Y Tế - VSEN Medical Equipment

**Version:** 1.0  
**Date:** 2025-12-28

---

## Mục lục

1. [Sitemap & Information Architecture](#sitemap)
2. [Design System](#design-system)

---

## <a name="sitemap"></a>1. Sitemap & Information Architecture

### 1.1 Site Structure (Phase 1)

```
Home (/)
│
├── Products (/products)
│   ├── Category: MRI (/products/mri)
│   ├── Category: CT (/products/ct)
│   ├── Category: Ultrasound (/products/ultrasound)
│   └── Product Detail (/products/{slug})
│
├── Services (/services)
│   ├── Maintenance (/services/maintenance)
│   ├── Repair (/services/repair)
│   └── Parts (/services/parts)
│
├── Solutions (/solutions)
│   ├── Cardiology (/solutions/cardiology)
│   ├── Oncology (/solutions/oncology)
│   └── ...
│
├── Insights (/insights)
│   ├── News (/insights/news)
│   │   └── Article Detail (/insights/news/{slug})
│   └── Case Studies (/insights/case-studies)
│
├── Resources (/resources)
│   └── Resource Detail (/resources/{slug})
│
├── About (/about)
│   ├── Company (/about/company)
│   └── Contact (/about/contact)
│
└── Search (/search?q={query})
```

**Lưu ý:** Events & Webinars, User Account pages sẽ có trong Phase 2.

### 1.2 Navigation

**Header:**
- Logo
- Mega Menu (Products, Services, Solutions, Education, Insights)
- Search Bar
- Contact/Request Quote Button

**Footer:**
- Products links
- Services links
- Company links
- Resources links
- Legal links (Privacy, Terms)
- Social media links

### 1.3 Key Pages

#### Homepage
- Hero section
- Featured products
- Solutions by specialty
- Latest news
- Trust indicators

#### Product Listing
- Sidebar filters
- Product grid/list
- Sort & pagination

#### Product Detail
- Image gallery
- Product info
- Specifications tabs
- Downloads
- Related products
- CTA buttons

#### Article Detail
- Featured image
- Title & meta
- Content
- Related articles
- Share buttons

---

## <a name="design-system"></a>2. Design System

### 2.1 Colors

**Primary:**
- Primary Blue: `#0066CC`
- Primary Dark: `#004499` (hover)
- Primary Light: `#E6F2FF` (backgrounds)

**Neutrals:**
- Black: `#000000` (primary text)
- Dark Gray: `#333333` (headings)
- Medium Gray: `#666666` (body text)
- Light Gray: `#CCCCCC` (borders)
- Lighter Gray: `#F5F5F5` (backgrounds)
- White: `#FFFFFF`

**Status:**
- Success: `#28A745`
- Warning: `#FFC107`
- Error: `#DC3545`
- Info: `#17A2B8`

### 2.2 Typography

**Font:** Inter hoặc System Font Stack
```
-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
```

**Sizes:**
- H1: `2.5rem` (40px)
- H2: `2rem` (32px)
- H3: `1.75rem` (28px)
- H4: `1.5rem` (24px)
- Body: `1rem` (16px)
- Small: `0.875rem` (14px)

**Weights:**
- Regular: 400
- Medium: 500
- Semibold: 600
- Bold: 700

### 2.3 Spacing

Base unit: 4px
- xs: `0.25rem` (4px)
- sm: `0.5rem` (8px)
- md: `1rem` (16px)
- lg: `1.5rem` (24px)
- xl: `2rem` (32px)
- 2xl: `3rem` (48px)

### 2.4 Components

#### Buttons

**Primary Button:**
```css
background: #0066CC;
color: #FFFFFF;
padding: 0.75rem 1.5rem;
border-radius: 0.375rem;
font-weight: 600;
```

**Secondary Button:**
```css
background: transparent;
color: #0066CC;
border: 2px solid #0066CC;
padding: 0.75rem 1.5rem;
border-radius: 0.375rem;
```

#### Forms

**Input:**
```css
width: 100%;
padding: 0.75rem;
border: 1px solid #CCCCCC;
border-radius: 0.375rem;
```

**Input Focus:**
```css
border-color: #0066CC;
box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
```

#### Cards

```css
background: #FFFFFF;
border: 1px solid #CCCCCC;
border-radius: 0.5rem;
padding: 1.5rem;
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
```

#### Modals

```css
background: #FFFFFF;
border-radius: 0.5rem;
padding: 2rem;
max-width: 500px;
width: 90%;
```

### 2.5 Icons

**Library:** Heroicons hoặc Lucide React

**Sizes:**
- Small: `1rem` (16px)
- Medium: `1.5rem` (24px)
- Large: `2rem` (32px)

### 2.6 Images

**Aspect Ratios:**
- Product Images: 16:9 hoặc 4:3
- Hero Images: 21:9
- Thumbnails: 1:1

**Formats:**
- Primary: WebP
- Fallback: JPEG
- Transparency: PNG

**Optimization:**
- Lazy loading
- Responsive images (srcset)
- Compression (80-90% quality)

### 2.7 Responsive Breakpoints

```css
/* Mobile: default (< 768px) */
/* Tablet: 768px and up */
@media (min-width: 768px) { ... }

/* Desktop: 1024px and up */
@media (min-width: 1024px) { ... }

/* Large Desktop: 1280px and up */
@media (min-width: 1280px) { ... }
```

### 2.8 CSS Variables

```css
:root {
  --color-primary: #0066CC;
  --color-primary-dark: #004499;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --border-radius: 0.375rem;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

---

## Design Principles

1. **Professional:** Phản ánh tính chuyên nghiệp ngành y tế
2. **Trustworthy:** Tạo niềm tin với khách hàng
3. **Accessible:** Tuân thủ WCAG 2.1 (Phase 2)
4. **Modern:** Giao diện hiện đại, dễ sử dụng
5. **Consistent:** Nhất quán trên mọi trang

---

## Notes

- Phase 1: Single language (Vietnamese or English)
- No multi-country selector in Phase 1
- Focus on core components only
- Advanced features (animations, complex interactions) in Phase 2
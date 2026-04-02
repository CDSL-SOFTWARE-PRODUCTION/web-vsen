# Proposal: Refactor Project Architecture towards Domain-Driven Design (DDD)

## 🎯 Context & Motivation
Our project has successfully passed the MVP phase (a standard Product Showcase Website) and is now rapidly evolving into a comprehensive **Business OS (ERP/CRM system)** for VSEN Medical.

Currently, we are using the default Laravel directory structure (`app/Models`, `app/Filament/Resources`, `resources/js/Pages`). As the scale of business operations expands, maintaining all diverse entities (HR, Accounting, E-commerce, Inventory, etc.) in a single "God Folder" will inevitably lead to:
- Codebase clutter (hundreds of models in one folder).
- Poor boundaries between completely unrelated business logic.
- Difficulty in assigning feature-based ownership.

## 🏗 Proposed Solution: Domain-Driven Modular Pattern
We will refactor the directory structure to group classes by their **Business Domain** rather than their technical patterns. 

This approach directly aligns with our **Model-Driven Documentation (MDD)** strategy present in the `model/` folder (defined as `entities.yaml`).

### 1. Backend Structure (Laravel & Filament)
Instead of flat directories, we will group Models, Services, and Filament Resources into sub-folders matching their domain:

**Before:**
```text
app/Models/Product.php
app/Models/User.php
app/Models/Contract.php
app/Filament/Resources/ProductResource.php
```

**After (Proposed):**
```text
app/
├── Models/
│   ├── Catalog/           # e.g., Product, Category, Brand
│   ├── CRM/               # e.g., Customer, Contact, QuoteRequest
│   └── HR/                # e.g., Employee, Contract, Department
│
└── Filament/
    ├── Clusters/          # Use Filament v3 Clusters for UI Grouping
    │   ├── CatalogCluster.php
    │   ├── CrmCluster.php
    │   └── HrCluster.php
    └── Resources/
        ├── Catalog/       # Placed inside CatalogCluster
        │   └── ProductResource.php
        └── CRM/
            └── CustomerResource.php
```

### 2. Frontend Structure (Inertia/React)
Similarly, the UI views should be domain-segregated so that the E-commerce storefront doesn't mix with internal portal views.

**Proposed:**
```text
resources/js/Pages/
├── Catalog/          # Storefront and Client views
│   ├── ProductList.tsx
│   └── ProductDetail.tsx
├── Portal/           # Client portal
│   ├── MyTickets.tsx
│   └── Invoices.tsx
└── Core/             # Shared pages
    └── Checkout.tsx
```

## 🛠 Execution Steps (Action Items)
1. [ ] **Backend Restructuring:** Move existing `app/Models` into their appropriate scope (e.g., move `Product.php` to `app/Models/Catalog/Product.php`).
2. [ ] **Namespace Updates:** Run global search-and-replace to fix any broken namespaces across the app caused by the model restructuring.
3. [ ] **Filament Clusters:** Create initial Filament Clusters (`CatalogCluster`, etc.) and move existing resources into them to clean up the lateral navigation menu.
4. [ ] **Frontend Restructuring:** Organize `resources/js/Pages` into scoped folders matching the backend modules.

## 🎉 Expected Outcomes
- **Clarity:** Developers immediately know exactly where a feature lives based on its business domain.
- **Scalability:** We can safely inject 100+ new features into the Business OS without fearing a bloated admin interface (thanks to Clusters) or a bloated backend.
- **Traceability:** Direct 1:1 mapping between `model/entities.yaml` and our live codebase folders.

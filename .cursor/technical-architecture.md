# Technical Architecture - LHDN Middleware

**Last Updated:** 2025-10-03  
**Architecture:** Hybrid Approach (Filament + Custom Blade)

---

## 🏗️ System Architecture

### High-Level Overview
```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
├─────────────────────────────────────────────────────────────┤
│  Landing Pages        │  User App (/app)   │  Admin Panel   │
│  (Blade + Tailwind)   │  (Blade + Tailwind)│  (/admin)      │
│  - Homepage           │  - Dashboard       │  - Companies   │
│  - Pricing            │  - Invoices        │  - Users       │
│  - Auth               │  - Settings        │  - Approvals   │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      MIDDLEWARE LAYER                        │
├─────────────────────────────────────────────────────────────┤
│  - Authentication (Laravel Breeze)                           │
│  - Email Verification                                        │
│  - EnsureCompanyOnboarded                                   │
│  - EnsureActiveSubscription                                 │
│  - Multi-tenant Data Isolation                              │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                       BUSINESS LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  - User Controllers (User App)                              │
│  - Filament Resources (Admin)                               │
│  - Authorization Policies                                   │
│  - Multi-tenant Scoping                                     │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                        DATA LAYER                            │
├─────────────────────────────────────────────────────────────┤
│  - Eloquent Models                                          │
│  - Database (MySQL)                                         │
│  - Multi-tenant Architecture                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Hybrid Approach Implementation

### Admin Panel (Filament) - UNCHANGED
- **Path:** `/admin`
- **Technology:** Filament 4
- **Purpose:** Complex data management for super admins
- **Features:**
  - User management
  - Company approval workflow
  - Invoice oversight
  - Payment tracking
  - System administration

### User App (Custom Blade) - NEW
- **Path:** `/app`
- **Technology:** Blade + Tailwind CSS + Alpine.js
- **Purpose:** Business user interface
- **Features:**
  - Company profile management
  - Invoice creation and management
  - LHDN credentials setup
  - Settings and preferences

---

## 🔐 Authentication & Authorization

### Authentication Flow
```
User Registration
    ↓
Email Verification (Laravel Breeze)
    ↓
Company Onboarding (Forced)
    ↓
Subscription Selection
    ↓
Payment Proof Upload
    ↓
Admin Approval (pending state)
    ↓
Access Granted (/app dashboard)
```

### User Roles
1. **Super Admin**
   - Access: `/admin` (Filament panel)
   - Can manage all companies and users
   - Full system access

2. **Business User**
   - Access: `/app` (Custom interface)
   - Can manage own company and invoices
   - Limited to own data

### Authorization Policies
- **InvoicePolicy:** Company-based data isolation
- **LhdnCredentialPolicy:** Credentials access control
- **Multi-tenant Scoping:** Automatic company_id filtering

---

## 🗄️ Database Architecture

### Multi-Tenancy Strategy
- **Tenant Key:** `company_id` on all tenant-scoped tables
- **Global Scope:** Applied automatically to all models
- **Super Admin Bypass:** Super admins see all data

### Core Tables
```sql
users (shared)
├── company_id (FK)
├── subscription_plan_id (FK)
└── subscription_status

companies (shared)
├── subscription_plan_id (FK)
└── company details

invoices (tenant-scoped)
├── company_id (FK)
├── lhdn_status (enum)
└── invoice details

invoice_items (tenant-scoped)
├── invoice_id (FK)
└── item details

lhdn_credentials (tenant-scoped)
├── company_id (FK)
└── API credentials

usage_logs (tenant-scoped)
├── company_id (FK)
└── usage tracking
```

---

## 🎨 Frontend Architecture

### Technology Stack
- **CSS Framework:** Tailwind CSS 3.x
- **JavaScript:** Alpine.js 3.x
- **Icons:** Heroicons (SVG)
- **Fonts:** Figtree (Google Fonts)

### Design System
```javascript
// Brand Colors (tailwind.config.js)
brand: {
  50: '#fef7f7',   // Lightest
  100: '#fdeaea',
  200: '#fad4d4',
  300: '#f6b3b3',
  400: '#f08585',
  500: '#bf4036',  // Primary
  600: '#a8352d',
  700: '#8b2a24',
  800: '#6e211c',
  900: '#511814',
  950: '#2d0f0c',  // Darkest
}
```

### Component Structure
```
resources/views/
├── layouts/
│   └── user-app.blade.php      # Main layout
├── user-app/
│   ├── dashboard.blade.php     # Dashboard
│   ├── company/                # Company management
│   ├── credentials/            # LHDN credentials
│   ├── invoices/               # Invoice management
│   └── settings/               # Settings pages
└── components/                 # Reusable components
```

---

## 🛣️ Routing Architecture

### Route Groups
```php
// Admin Routes (Filament)
/admin/* → Filament Panel

// User App Routes (Custom)
Route::prefix('app')
  ->middleware(['auth', 'verified', 'subscription.paid'])
  ->name('user.')
  ->group(function () {
    // User app routes
  });

// Public Routes
/ → Landing pages
/auth/* → Authentication
```

### Middleware Stack
1. **Authentication:** `auth`
2. **Email Verification:** `verified`
3. **Subscription Active:** `subscription.paid`
4. **Company Onboarded:** `onboarded` (if needed)

---

## 🔧 Development Environment

### Local Development
```bash
# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Compile assets
npm run dev

# Database
php artisan migrate
php artisan db:seed
```

### Key Commands
```bash
# Create user controllers
php artisan make:controller User/UserDashboardController

# Create policies
php artisan make:policy InvoicePolicy --model=Invoice

# Clear caches
php artisan optimize:clear
```

---

## 📊 Performance Considerations

### Database Optimization
- **Eager Loading:** `with()` for relationships
- **Indexes:** Proper indexing on company_id, status fields
- **Pagination:** Implemented for large datasets

### Frontend Optimization
- **Asset Compilation:** Vite for fast builds
- **CSS Purging:** Tailwind removes unused styles
- **Alpine.js:** Lightweight JavaScript framework
- **Progressive Enhancement:** Works without JavaScript

### Caching Strategy
- **Route Caching:** `php artisan route:cache`
- **Config Caching:** `php artisan config:cache`
- **View Caching:** `php artisan view:cache`
- **Redis Ready:** Prepared for Redis caching

---

## 🔒 Security Implementation

### Data Protection
- **CSRF Protection:** Laravel built-in
- **XSS Protection:** Blade escaping
- **SQL Injection:** Eloquent ORM protection
- **Multi-tenant Isolation:** Company-based scoping

### Authentication Security
- **Password Hashing:** Laravel bcrypt
- **Email Verification:** Required for access
- **Session Management:** Laravel sessions
- **Rate Limiting:** Built-in throttling

---

## 🚀 Deployment Considerations

### Production Requirements
- **PHP:** 8.2+
- **MySQL:** 8.0+
- **Web Server:** Nginx/Apache
- **SSL:** Required for production

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Asset Pipeline
```bash
# Production build
npm run build

# Optimize Laravel
php artisan optimize
```

---

## 📈 Scalability

### Horizontal Scaling
- **Stateless Application:** Ready for load balancing
- **Database:** MySQL with read replicas
- **Caching:** Redis cluster support
- **File Storage:** S3-compatible storage

### Vertical Scaling
- **PHP-FPM:** Optimized for high concurrency
- **Database:** Optimized queries and indexes
- **Memory:** Efficient Laravel caching
- **CPU:** Optimized asset compilation

---

## 🔄 Maintenance & Updates

### Code Organization
- **Controllers:** Separated by functionality
- **Policies:** Centralized authorization logic
- **Views:** Modular Blade templates
- **Assets:** Organized by feature

### Testing Strategy
- **Unit Tests:** Model and policy testing
- **Feature Tests:** Controller and route testing
- **Browser Tests:** User interface testing
- **Integration Tests:** API and database testing

---

## 📝 Documentation

### Code Documentation
- **PHPDoc:** All methods documented
- **README:** Setup and deployment guide
- **API Docs:** Route documentation
- **Architecture:** This document

### User Documentation
- **Admin Guide:** Filament panel usage
- **User Guide:** Business user interface
- **API Guide:** LHDN integration
- **Troubleshooting:** Common issues

---

**Architecture Status:** Production-ready hybrid implementation with scalable multi-tenant design.

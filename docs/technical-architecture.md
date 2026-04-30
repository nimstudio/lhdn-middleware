# Technical Architecture Documentation

## 🏗️ **System Architecture Overview**

The LHDN Middleware SaaS is built on Laravel 11 with a multi-tenant architecture, providing a robust and scalable solution for Malaysian companies to manage invoices and integrate with LHDN's MyInvois system.

---

## 🎯 **Architecture Principles**

### **Multi-Tenant SaaS Design**
- **Data Isolation**: All data is scoped by `company_id`
- **Shared Database**: Single database with tenant isolation
- **Scalable**: Designed to handle multiple companies efficiently
- **Secure**: Comprehensive access control and data protection

### **Layered Architecture**
```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                       │
├─────────────────────────────────────────────────────────────┤
│  Landing Pages        │  User App (/app)   │  Admin Panel   │
│  (Blade + Tailwind)   │  (Custom Blade)    │  (/admin)      │
│  - Homepage           │  - Dashboard       │  - Companies   │
│  - Pricing            │  - Invoices        │  - Approvals   │
│  - Auth               │  - Customers       │  - Users       │
│                       │  - Settings        │  - Analytics   │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    MIDDLEWARE LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  - Authentication (Laravel Sanctum)                         │
│  - Email Verification                                        │
│  - Subscription Guard (subscription.paid)                   │
│  - Company Guard (company.required)                         │
│  - TIN Validation Guard (tin.verified)                      │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     BUSINESS LOGIC                          │
├─────────────────────────────────────────────────────────────┤
│  - Invoice Management Service                                │
│  - TIN Validation Service                                    │
│  - MyInvois SDK Service                                      │
│  - PDF Generation Service                                    │
│  - Number to Words Service                                   │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      DATA LAYER                             │
├─────────────────────────────────────────────────────────────┤
│  - MySQL Database (Multi-tenant)                            │
│  - File Storage (PDFs, Logos)                               │
│  - Activity Logs                                            │
│  - Media Library                                            │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗄️ **Database Architecture**

### **Core Tables Structure**

#### **Users Table**
```sql
users
├── id (bigint, PK)
├── name (string)
├── email (string, unique)
├── email_verified_at (timestamp, nullable)
├── password (string)
├── company_id (bigint, FK, nullable)
├── subscription_plan_id (bigint, FK, nullable)
├── subscription_status (enum: pending, active, expired, cancelled)
├── subscription_payment_proof (string, nullable)
├── subscription_approved_by (bigint, FK, nullable)
├── subscription_approved_at (timestamp, nullable)
├── remember_token (string, nullable)
├── timestamps
└── soft_deletes
```

#### **Companies Table (Tenant)**
```sql
companies
├── id (bigint, PK)
├── uuid (string, unique)
├── name (string)
├── registration_number (string, unique) -- SSM number
├── tin_number (string, unique) -- Tax Identification Number
├── tin_verified_at (timestamp, nullable)
├── tin_status (enum: valid, invalid, pending)
├── last_tin_check_at (timestamp, nullable)
├── tin_source (enum: manual, sdk)
├── email (string)
├── phone (string)
├── address_line_1 (string)
├── address_line_2 (string, nullable)
├── city (string)
├── state_id (bigint, FK)
├── postcode (string)
├── business_type_id (bigint, FK) -- MSIC code
├── country (string, default: 'Malaysia')
├── status (enum: pending, active, suspended, cancelled)
├── onboarding_completed (boolean, default: false)
├── subscription_plan_id (bigint, FK, nullable)
├── subscription_status (enum: pending, active, expired, cancelled)
├── subscription_starts_at (date, nullable)
├── subscription_ends_at (date, nullable)
├── subscription_payment_proof (string, nullable)
├── subscription_approved_by (bigint, FK, nullable)
├── subscription_approved_at (timestamp, nullable)
├── invoice_prefix (string, nullable)
├── default_tax_rates (json, nullable)
├── pdf_settings (json, nullable)
├── timestamps
└── soft_deletes
```

#### **LHDN Credentials Table**
```sql
lhdn_credentials
├── id (bigint, PK)
├── company_id (bigint, FK, unique)
├── client_id (string)
├── client_secret (string)
├── access_token (text, nullable)
├── token_type (string, nullable)
├── mode (enum: sandbox, production)
├── last_token_refresh (timestamp, nullable)
├── token_expires_at (timestamp, nullable)
├── status (enum: active, expired, invalid)
├── created_by (bigint, FK)
├── updated_by (bigint, FK)
├── timestamps
└── soft_deletes
```

#### **Invoices Table**
```sql
invoices
├── id (bigint, PK)
├── company_id (bigint, FK)
├── customer_id (bigint, FK, nullable)
├── uuid (string, unique)
├── invoice_number (string)
├── invoice_date (date)
├── due_date (date)
├── customer_name (string)
├── customer_tin (string, nullable)
├── customer_registration_number (string, nullable)
├── customer_email (string, nullable)
├── customer_phone (string, nullable)
├── customer_address (text, nullable)
├── customer_street_address (string, nullable)
├── customer_city (string, nullable)
├── customer_state (string, nullable)
├── customer_postal_code (string, nullable)
├── customer_country (string, nullable)
├── currency (string, default: 'MYR')
├── subtotal (decimal:2)
├── tax_amount (decimal:2)
├── discount_amount (decimal:2)
├── total_amount (decimal:2)
├── invoice_status (enum: draft, sent, paid, overdue, cancelled)
├── payment_method (string, nullable)
├── notes (text, nullable)
├── lhdn_status (enum: pending, submitted, approved, rejected)
├── lhdn_submission_id (string, nullable)
├── lhdn_submitted_at (timestamp, nullable)
├── lhdn_response (json, nullable)
├── lhdn_error_message (text, nullable)
├── submitted_by (bigint, FK, nullable)
├── created_by (bigint, FK)
├── timestamps
└── soft_deletes
```

#### **Invoice Items Table**
```sql
invoice_items
├── id (bigint, PK)
├── invoice_id (bigint, FK)
├── description (string)
├── quantity (decimal:2)
├── unit_price (decimal:2)
├── tax_rate (decimal:2)
├── tax_amount (decimal:2)
├── total_amount (decimal:2)
├── sort_order (integer, default: 0)
├── timestamps
└── soft_deletes
```

#### **Customers Table**
```sql
customers
├── id (bigint, PK)
├── company_id (bigint, FK)
├── name (string)
├── email (string, nullable)
├── phone (string, nullable)
├── street_address (string, nullable)
├── city (string, nullable)
├── state_id (bigint, FK, nullable)
├── postal_code (string, nullable)
├── country (string, default: 'MY')
├── tin (string, nullable)
├── document_type (string, nullable)
├── document_number (string, nullable)
├── is_active (boolean, default: true)
├── timestamps
└── soft_deletes
```

### **Supporting Tables**
- **states**: Malaysian states
- **msics**: Malaysian Standard Industrial Classification codes
- **subscription_plans**: Available subscription plans
- **usage_logs**: API usage tracking

---

## 🔐 **Security Architecture**

### **Authentication & Authorization**
```php
// Middleware Stack
Route::middleware(['auth', 'verified', 'subscription.paid'])
    ->prefix('app')
    ->group(function () {
        // Company required routes
        Route::middleware('company.required')->group(function () {
            // TIN verified routes
            Route::middleware('tin.verified')->group(function () {
                Route::resource('invoices', UserInvoiceController::class);
                Route::resource('customers', CustomerController::class);
            });
            
            Route::resource('credentials', UserCredentialsController::class);
        });
    });
```

### **Data Isolation**
- **Company Scoping**: All queries automatically scoped by `company_id`
- **Tenant Isolation**: Users can only access their company's data
- **Super Admin Bypass**: Super admins can access all data for management

### **Input Validation**
- **Form Validation**: Comprehensive validation rules for all inputs
- **TIN Uniqueness**: Prevents duplicate TIN numbers across companies
- **SQL Injection Protection**: Eloquent ORM with parameterized queries
- **XSS Protection**: Blade templating with automatic escaping

---

## 🔄 **Service Layer Architecture**

### **Core Services**

#### **MyInvoisSdkService**
```php
class MyInvoisSdkService
{
    public function getClient(LhdnCredential $credentials): MyInvoisClient
    public function authenticate(LhdnCredential $credentials): array
    public function ensureValidToken(LhdnCredential $credentials): bool
}
```

**Responsibilities:**
- LHDN API client management
- Token authentication and refresh
- API request/response handling
- Error handling and logging

#### **TinValidationService**
```php
class TinValidationService
{
    public function validateCompanyTin(Company $company): array
    public function hasValidTin(Company $company): bool
    public function requiresTinValidation(Company $company): bool
}
```

**Responsibilities:**
- TIN validation logic
- LHDN TIN search integration
- TIN uniqueness validation
- Validation status management

#### **InvoicePdfService**
```php
class InvoicePdfService
{
    public function generatePdf(Invoice $invoice): string
    public function generatePreview(Invoice $invoice): string
    public function getPdfSettings(Company $company): array
}
```

**Responsibilities:**
- PDF generation using DomPDF
- Template rendering
- Company branding integration
- PDF customization

#### **InvoiceNumberGenerator**
```php
class InvoiceNumberGenerator
{
    public function generate(Company $company): string
    public function getNextNumber(Company $company): int
}
```

**Responsibilities:**
- Unique invoice number generation
- Prefix and suffix handling
- Sequential numbering
- Format customization

---

## 🌐 **API Integration Architecture**

### **LHDN MyInvois Integration**
```php
// Authentication Flow
$client = new MyInvoisClient($clientId, $clientSecret, $isProduction);
$client->login();
$accessToken = $client->getAccessToken();

// TIN Validation
$taxpayerService = new TaxPayerService($client, $isProduction);
$tin = $taxpayerService->searchTaxPayerTin('', 'BRN', $registrationNumber, '2');

// Invoice Submission
$invoiceService = new InvoiceService($client, $isProduction);
$response = $invoiceService->submitInvoice($invoiceData);
```

### **Error Handling**
- **API Errors**: Comprehensive error handling for LHDN API responses
- **Network Errors**: Retry logic for network failures
- **Validation Errors**: User-friendly error messages
- **Logging**: Detailed logging for debugging and monitoring

---

## 📊 **Data Flow Architecture**

### **Invoice Creation Flow**
```
1. User Input → Form Validation
2. Validation Success → Invoice Creation
3. Invoice Created → Auto-calculation
4. Calculation Complete → PDF Generation
5. PDF Generated → LHDN Submission (optional)
6. Submission Complete → Status Update
```

### **TIN Validation Flow**
```
1. Company Save → TIN Uniqueness Check
2. Uniqueness Valid → LHDN Validation (if credentials exist)
3. LHDN Search → TIN Comparison
4. TIN Match → Status Update
5. TIN Mismatch → Conflict Resolution
6. Resolution Complete → Status Update
```

### **Subscription Flow**
```
1. User Registration → Plan Selection
2. Plan Selected → Payment Proof Upload
3. Proof Uploaded → Admin Review
4. Admin Approval → Subscription Activation
5. Activation Complete → Feature Access
```

---

## 🚀 **Performance Architecture**

### **Caching Strategy**
- **Database Queries**: Eloquent query caching
- **API Responses**: LHDN API response caching
- **PDF Generation**: Template caching
- **Static Assets**: CDN caching

### **Database Optimization**
- **Indexes**: Optimized indexes on frequently queried fields
- **Foreign Keys**: Proper foreign key constraints
- **Query Optimization**: Efficient Eloquent queries
- **Connection Pooling**: Database connection optimization

### **File Storage**
- **PDF Storage**: Organized file storage structure
- **Media Library**: Spatie Media Library for file management
- **CDN Integration**: Content delivery network for static assets
- **Backup Strategy**: Automated backup system

---

## 🔧 **Development Architecture**

### **Code Organization**
```
app/
├── Console/Commands/          # Artisan commands
├── Http/
│   ├── Controllers/User/      # User-facing controllers
│   ├── Middleware/            # Custom middleware
│   └── Requests/              # Form request validation
├── Models/                    # Eloquent models
├── Services/                  # Business logic services
├── Policies/                  # Authorization policies
└── Providers/                 # Service providers
```

### **Frontend Architecture**
```
resources/
├── views/
│   ├── user-app/              # User application views
│   ├── components/            # Reusable Blade components
│   └── layouts/               # Layout templates
├── css/                       # Tailwind CSS
├── js/                        # Alpine.js components
└── lang/                      # Localization files
```

### **Configuration Management**
- **Environment Variables**: Secure configuration management
- **Service Providers**: Dependency injection configuration
- **Middleware Registration**: Middleware stack configuration
- **Route Caching**: Optimized route caching

---

## 📈 **Monitoring & Logging**

### **Application Logging**
```php
// Laravel Log Channels
'channels' => [
    'laravel' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
    ],
    'myinvois' => [
        'driver' => 'daily',
        'path' => storage_path('logs/myinvois.log'),
    ],
]
```

### **Activity Logging**
- **User Actions**: All user actions logged with Spatie Activity Log
- **Company Scoping**: Activity logs scoped by company
- **API Calls**: LHDN API calls logged separately
- **Error Tracking**: Comprehensive error logging

### **Performance Monitoring**
- **Query Performance**: Database query monitoring
- **API Response Times**: LHDN API performance tracking
- **PDF Generation**: PDF generation performance
- **Memory Usage**: Application memory monitoring

---

## 🔄 **Deployment Architecture**

### **Environment Configuration**
```bash
# Production Environment
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### **Server Requirements**
- **PHP**: 8.2+ with required extensions
- **MySQL**: 8.0+ with InnoDB engine
- **Redis**: For caching and queues
- **Web Server**: Nginx or Apache
- **SSL**: HTTPS required for production

### **Deployment Process**
1. **Code Deployment**: Git-based deployment
2. **Database Migration**: Automated migration process
3. **Asset Compilation**: Frontend asset compilation
4. **Cache Clearing**: Application cache clearing
5. **Queue Restart**: Background job queue restart

---

## 🔮 **Scalability Considerations**

### **Horizontal Scaling**
- **Load Balancing**: Multiple application servers
- **Database Scaling**: Read replicas and connection pooling
- **CDN Integration**: Content delivery network
- **Queue Workers**: Distributed background job processing

### **Vertical Scaling**
- **Server Resources**: CPU and memory optimization
- **Database Optimization**: Query optimization and indexing
- **Caching**: Redis caching for performance
- **File Storage**: Optimized file storage strategy

### **Future Enhancements**
- **Microservices**: Service decomposition for scalability
- **API Gateway**: Centralized API management
- **Event Sourcing**: Event-driven architecture
- **CQRS**: Command Query Responsibility Segregation

---

*This technical architecture documentation provides a comprehensive overview of the system's technical implementation, design patterns, and architectural decisions.*




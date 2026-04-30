# LHDN Middleware SaaS - Complete System Overview

## 🏢 **System Overview**

**LHDN Middleware SaaS** is a comprehensive Laravel 11-based SaaS application that enables Malaysian companies to manage invoices and submit them to LHDN (Malaysian Inland Revenue Board) through the MyInvois system. The platform provides a complete invoice management solution with automatic TIN validation, PDF generation, and seamless LHDN integration.

### **Key Technologies**
- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Blade + TailwindCSS + AlpineJS
- **Database**: MySQL 8.0+
- **PDF Generation**: DomPDF
- **LHDN Integration**: klsheng/myinvois-php-sdk
- **Admin Panel**: Filament 3.x
- **Media Management**: Spatie Media Library
- **Activity Logging**: Spatie Activity Log

---

## 🚀 **Complete User Journey**

### **1. Registration & Subscription**
- User registers for account
- Selects subscription plan (Starter RM99, Business RM299, Enterprise RM999)
- Uploads payment proof
- Admin approves subscription
- User gains access to dashboard

### **2. Company Setup (Mandatory)**
- User must complete company profile before accessing core features
- **Required Fields**: Company name, registration number (SSM), TIN, address, business type (MSIC)
- **Validation**: TIN and registration number uniqueness across all companies
- **Automatic TIN Validation**: If LHDN credentials exist, TIN is automatically validated with LHDN

### **3. LHDN Credentials (Optional)**
- User can add LHDN MyInvois API credentials
- **Modes**: Sandbox (testing) or Production
- **Automatic Authentication**: Credentials are tested immediately upon save
- **Token Management**: Access tokens are stored and automatically refreshed
- **TIN Validation**: Once credentials are active, company TIN is validated with LHDN

### **4. Core Features Access**
- **Invoice Management**: Create, edit, view, and manage invoices
- **Customer Management**: Manage customer database
- **PDF Generation**: Generate professional invoice PDFs
- **LHDN Submission**: Submit invoices directly to LHDN (if credentials configured)
- **Settings**: Customize PDF templates, invoice settings, and company preferences

---

## 🏗️ **System Architecture**

### **Multi-Tenant SaaS Structure**
```
┌─────────────────────────────────────────────────────────────┐
│                     USER INTERFACE                          │
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

## 📊 **Database Schema**

### **Core Tables**

#### **Users Table**
- Standard Laravel auth fields
- `company_id` (FK to companies)
- `subscription_plan_id` (FK to subscription_plans)
- `subscription_status` (pending, active, expired, cancelled)
- `subscription_payment_proof` (file path)
- `subscription_approved_by` (FK to users)
- `subscription_approved_at` (timestamp)

#### **Companies Table (Tenant)**
- `uuid` (unique identifier)
- `name`, `registration_number` (SSM), `tin_number` (unique)
- `email`, `phone`, `address_line_1`, `address_line_2`
- `city`, `state_id` (FK), `postcode`, `country`
- `business_type_id` (FK to msics - MSIC code)
- `status` (pending, active, suspended, cancelled)
- `onboarding_completed` (boolean)
- **TIN Validation Fields**:
  - `tin_verified_at`, `tin_status` (valid, invalid, pending)
  - `last_tin_check_at`, `tin_source` (manual, sdk)
- **Subscription Fields**:
  - `subscription_plan_id`, `subscription_status`
  - `subscription_starts_at`, `subscription_ends_at`
  - `subscription_payment_proof`, `subscription_approved_by`
- **Settings Fields**:
  - `invoice_prefix`, `default_tax_rates` (JSON)
  - `pdf_settings` (JSON)

#### **LHDN Credentials Table**
- `company_id` (unique FK)
- `client_id`, `client_secret`, `mode` (sandbox/production)
- `access_token`, `token_type`
- `last_token_refresh`, `token_expires_at`
- `status` (active, expired, invalid)
- `created_by`, `updated_by` (FK to users)

#### **Invoices Table**
- `company_id` (FK), `customer_id` (FK)
- `uuid`, `invoice_number`, `invoice_date`, `due_date`
- **Customer Details** (denormalized for performance):
  - `customer_name`, `customer_tin`, `customer_email`, `customer_phone`
  - `customer_address`, `customer_street_address`, `customer_city`
  - `customer_state`, `customer_postal_code`, `customer_country`
- **Financial Fields**:
  - `currency`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`
- **Status Fields**:
  - `invoice_status` (draft, sent, paid, overdue, cancelled)
  - `payment_method`, `notes`
- **LHDN Integration Fields**:
  - `lhdn_status`, `lhdn_submission_id`, `lhdn_submitted_at`
  - `lhdn_response` (JSON), `lhdn_error_message`
- `submitted_by`, `created_by` (FK to users)

#### **Invoice Items Table**
- `invoice_id` (FK), `description`, `quantity`
- `unit_price`, `tax_rate`, `tax_amount`, `total_amount`
- `sort_order`

#### **Customers Table**
- `company_id` (FK), `name`, `email`, `phone`
- `street_address`, `city`, `state_id` (FK), `postal_code`, `country`
- `tin`, `document_type`, `document_number`
- `is_active` (boolean)

#### **Supporting Tables**
- **States**: Malaysian states (FK from companies, customers)
- **MSICs**: Malaysian Standard Industrial Classification codes
- **Subscription Plans**: Starter, Business, Enterprise plans
- **Usage Logs**: Track API usage and limits

---

## 🔐 **Security & Access Control**

### **Middleware Stack**
1. **Authentication** (`auth`): User must be logged in
2. **Email Verification** (`verified`): Email must be verified
3. **Subscription Guard** (`subscription.paid`): Active subscription required
4. **Company Guard** (`company.required`): Company profile must exist
5. **TIN Validation Guard** (`tin.verified`): TIN must be validated (if LHDN credentials exist)

### **Route Protection**
```php
// Public routes
/ (landing page)
/auth/* (authentication)

// Subscription required
/app/* (all user app routes)

// Company required
/app/credentials/*
/app/customers/*
/app/invoices/*

// TIN validation required (if LHDN credentials exist)
/app/customers/*
/app/invoices/*
```

### **Data Security**
- **Multi-tenant isolation**: All data scoped by company_id
- **TIN uniqueness**: Prevents duplicate TIN numbers across companies
- **Token management**: Secure storage and automatic refresh of LHDN tokens
- **Activity logging**: Complete audit trail of all actions
- **Input validation**: Comprehensive validation on all user inputs

---

## 🎯 **Core Features**

### **1. Invoice Management**
- **Create/Edit Invoices**: Full CRUD operations with line items
- **Auto-calculation**: Automatic tax and total calculations
- **Invoice Numbering**: Configurable invoice number generation
- **Status Management**: Draft, sent, paid, overdue, cancelled
- **Payment Tracking**: Mark invoices as paid with payment method
- **Bulk Operations**: Bulk actions on multiple invoices

### **2. Customer Management**
- **Customer Database**: Store and manage customer information
- **Search & Filter**: Advanced search and filtering capabilities
- **Bulk Actions**: Import/export, bulk updates
- **TIN Validation**: Customer TIN validation (if provided)

### **3. PDF Generation**
- **Professional Templates**: Customizable invoice PDF templates
- **Company Branding**: Upload company logo and customize colors
- **Multiple Formats**: A4, Letter, custom sizes
- **Preview Mode**: Preview PDF before generation
- **Batch Generation**: Generate multiple PDFs at once

### **4. LHDN Integration**
- **MyInvois SDK**: Full integration with LHDN MyInvois system
- **Automatic Authentication**: Token management and refresh
- **TIN Validation**: Real-time TIN validation with LHDN
- **Invoice Submission**: Direct submission to LHDN
- **Status Tracking**: Track submission status and responses
- **Error Handling**: Comprehensive error handling and logging

### **5. Settings & Customization**
- **Company Settings**: Update company information and preferences
- **PDF Settings**: Customize PDF templates, logos, colors
- **Invoice Settings**: Configure invoice numbering, tax rates
- **LHDN Credentials**: Manage API credentials and test connections
- **User Profile**: Update user profile and preferences

---

## 🔄 **Business Logic**

### **TIN Validation Flow**
1. **Company Creation**: User enters TIN and registration number
2. **Uniqueness Check**: System validates TIN/registration number uniqueness
3. **LHDN Validation** (if credentials exist):
   - Search TIN using BRN (registration number) via MyInvois SDK
   - Compare returned TIN with stored TIN
   - Update TIN if different (and unique)
   - Flag as invalid if conflict detected
4. **Middleware Enforcement**: Block access to core features if TIN invalid

### **Invoice Workflow**
1. **Create Invoice**: User creates invoice with customer and line items
2. **Auto-calculation**: System calculates taxes and totals
3. **PDF Generation**: Generate professional PDF invoice
4. **LHDN Submission** (optional):
   - Validate company TIN is valid
   - Submit invoice to LHDN via MyInvois SDK
   - Track submission status and response
5. **Status Updates**: Update invoice status based on payment/submission

### **Subscription Management**
1. **Plan Selection**: User selects subscription plan
2. **Payment Proof**: User uploads payment proof
3. **Admin Approval**: Admin reviews and approves subscription
4. **Access Activation**: User gains access to features based on plan
5. **Usage Tracking**: System tracks usage against plan limits

---

## 📱 **User Interface**

### **Dashboard**
- **Overview Stats**: Invoice counts, revenue, recent activity
- **Quick Actions**: Create invoice, add customer, view reports
- **Recent Invoices**: Latest invoices with status indicators
- **Onboarding Widget**: Guided setup for new users
- **LHDN Status**: TIN validation status and credential status

### **Invoice Management**
- **List View**: Paginated invoice list with filters and search
- **Create/Edit Form**: Intuitive form with line items
- **PDF Preview**: Real-time PDF preview
- **Bulk Actions**: Select multiple invoices for bulk operations

### **Customer Management**
- **Customer List**: Searchable and filterable customer database
- **Customer Form**: Comprehensive customer information form
- **Import/Export**: CSV import/export functionality
- **Bulk Actions**: Bulk update and delete operations

### **Settings**
- **Company Profile**: Update company information
- **PDF Customization**: Upload logo, customize colors and layout
- **LHDN Credentials**: Manage API credentials with test connection
- **Invoice Settings**: Configure numbering and tax rates

---

## 🛠️ **Technical Implementation**

### **Services**
- **MyInvoisSdkService**: Handles LHDN API integration
- **TinValidationService**: Manages TIN validation logic
- **InvoicePdfService**: Handles PDF generation
- **InvoiceNumberGenerator**: Generates unique invoice numbers
- **NumberToWordsService**: Converts numbers to words (for invoices)

### **Middleware**
- **EnsureSubscriptionPaid**: Validates active subscription
- **EnsureHasCompany**: Ensures company profile exists
- **TinVerified**: Ensures TIN is validated (if LHDN credentials exist)

### **Models & Relationships**
- **User** → **Company** (one-to-one)
- **Company** → **LhdnCredential** (one-to-one)
- **Company** → **Invoices** (one-to-many)
- **Company** → **Customers** (one-to-many)
- **Invoice** → **InvoiceItems** (one-to-many)
- **Invoice** → **Customer** (many-to-one)

### **API Integration**
- **MyInvois SDK**: Full integration with LHDN MyInvois system
- **Authentication**: OAuth2 token-based authentication
- **TIN Search**: Search taxpayer TIN using BRN
- **Invoice Submission**: Submit invoices to LHDN
- **Error Handling**: Comprehensive error handling and logging

---

## 📈 **Analytics & Reporting**

### **Dashboard Metrics**
- Total invoices created
- Total revenue generated
- Recent invoice activity
- TIN validation status
- LHDN submission success rate

### **Activity Logging**
- User actions logged with Spatie Activity Log
- Company-scoped logging for multi-tenancy
- Detailed audit trail for compliance
- API request/response logging

### **Usage Tracking**
- API usage tracking against plan limits
- Feature usage analytics
- Performance monitoring
- Error tracking and reporting

---

## 🚀 **Deployment & Operations**

### **Environment Requirements**
- PHP 8.2+
- MySQL 8.0+
- Redis (for queues and caching)
- Node.js (for frontend build)
- Composer (for PHP dependencies)

### **Configuration**
- Environment variables for database, mail, queue
- LHDN API credentials configuration
- File storage configuration
- Logging configuration

### **Monitoring**
- Application logs (Laravel logs)
- MyInvois API logs (separate channel)
- Error tracking and reporting
- Performance monitoring

---

## 🔮 **Future Enhancements**

### **Planned Features**
- **API Access**: RESTful API for third-party integrations
- **Advanced Reporting**: Detailed analytics and reports
- **Multi-currency Support**: Support for multiple currencies
- **Recurring Invoices**: Automated recurring invoice generation
- **Payment Integration**: Direct payment processing
- **Mobile App**: Native mobile application
- **Advanced PDF Templates**: More PDF template options
- **Bulk Import**: CSV import for invoices and customers

### **Technical Improvements**
- **Queue System**: Background job processing
- **Caching**: Redis caching for performance
- **CDN Integration**: Content delivery network
- **Backup System**: Automated database backups
- **Monitoring**: Advanced application monitoring
- **Security**: Enhanced security features

---

## 📞 **Support & Documentation**

### **User Documentation**
- User guides and tutorials
- Video tutorials
- FAQ section
- Support ticket system

### **Developer Documentation**
- API documentation
- Integration guides
- Code documentation
- Deployment guides

### **Admin Documentation**
- Admin panel guides
- User management
- System configuration
- Troubleshooting guides

---

*This documentation provides a comprehensive overview of the LHDN Middleware SaaS system. For specific implementation details, refer to the code documentation and API references.*




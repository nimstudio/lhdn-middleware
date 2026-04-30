# System Architecture Documentation

## Overview
Multi-tenant Laravel 11 SaaS application with two separate Filament 3 panels for user and admin interfaces.

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
├─────────────────────────────────────────────────────────────┤
│  Landing Pages        │  App Panel (/app)  │  Admin Panel   │
│  (Blade + Tailwind)   │  (Filament 3)      │  (/admin)      │
│  - Homepage           │  - Dashboard       │  - Companies   │
│  - Pricing            │  - Invoices        │  - Approvals   │
│  - Auth               │  - Settings        │  - Users       │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      MIDDLEWARE LAYER                        │
├─────────────────────────────────────────────────────────────┤
│  - Authentication (Sanctum)                                  │
│  - Email Verification                                        │
│  - EnsureCompanyOnboarded                                   │
│  - EnsureActiveSubscription                                 │
│  - CheckInvoiceLimit                                        │
│  - TenantScope (Multi-tenancy)                              │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                        │
├─────────────────────────────────────────────────────────────┤
│  Controllers        │  Services           │  Jobs           │
│  ----------------   │  -----------------  │  --------------  │
│  - Landing          │  - LhdnService      │  - SubmitInvoice│
│  - Onboarding       │  - UsageTracking    │  - ExpiryRemind │
│  - Subscription     │  - Subscription     │                 │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                       DOMAIN LAYER                           │
├─────────────────────────────────────────────────────────────┤
│  Models & Business Logic                                    │
│  - User (with company relationship)                         │
│  - Company (tenant root)                                    │
│  - Invoice, InvoiceItem                                     │
│  - SubscriptionPlan                                         │
│  - LhdnCredential (encrypted)                               │
│  - UsageLog                                                 │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      DATA LAYER                              │
├─────────────────────────────────────────────────────────────┤
│  MySQL Database     │  Redis Cache       │  File Storage    │
│  - All tables       │  - Sessions        │  - Payment proofs│
│  - Relationships    │  - Cache           │  - Invoices PDF  │
│  - Indexes          │  - Queue jobs      │                  │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                         │
├─────────────────────────────────────────────────────────────┤
│  - LHDN MyInvois API (UAT / Production)                     │
│  - Email Service (SMTP)                                     │
└─────────────────────────────────────────────────────────────┘
```

---

## Multi-Tenancy Strategy

### Tenant Isolation
- **Tenant Key:** `company_id` on all tenant-scoped tables
- **Global Scope:** Applied automatically to all models
- **Super Admin Bypass:** Super admins see all data

### Implementation
```php
// In base model or trait
protected static function booted()
{
    static::addGlobalScope('company', function (Builder $query) {
        if (auth()->check() && !auth()->user()->is_super_admin) {
            $query->where('company_id', auth()->user()->company_id);
        }
    });
}
```

### Tenant-Scoped Tables
- invoices
- invoice_items
- lhdn_credentials
- usage_logs
- activity_log (custom company_id field)

### Shared Tables (No Tenant Scope)
- users (linked via company_id)
- companies
- subscription_plans
- notifications

---

## Authentication Flow

```
User Registration
    ↓
Email Verification
    ↓
Company Onboarding (forced)
    ↓
Subscription Selection
    ↓
Payment Proof Upload
    ↓
Admin Approval (pending state)
    ↓
Access Granted (/app dashboard)
```

### Middleware Stack for Protected Routes
```php
Route::middleware([
    'auth',           // Must be authenticated
    'verified',       // Email verified
    'onboarded',      // Company information completed
    'subscribed',     // Active subscription
])->group(function () {
    // Protected app routes
});
```

---

## Filament Panel Architecture

### Panel 1: User Application (/app)
**Purpose:** Main user interface for invoice management

**Access:** Users with active subscriptions

**Resources:**
- Invoice (full CRUD)

**Pages:**
- Dashboard (with widgets)
- ProfileSettings
- CompanySettings
- LhdnCredentials
- SubscriptionInfo

**Widgets:**
- MonthlyUsageWidget
- RecentInvoicesWidget
- SubmissionStatsWidget

### Panel 2: Super Admin (/admin)
**Purpose:** System administration and approval workflow

**Access:** Users with `is_super_admin = true`

**Resources:**
- Company
- SubscriptionRequest
- SubscriptionPlan
- User
- ActivityLog

**Widgets:**
- PendingSubscriptionsWidget
- SystemStatsWidget

---

## Database Design Principles

### Relationships
```
User ────────┐
             ↓ (belongs to)
Company ─────┼─────> SubscriptionPlan
             │
             ├──> LhdnCredential (one-to-one)
             ├──> Invoice (one-to-many)
             └──> UsageLog (one-to-many)

Invoice ─────> InvoiceItem (one-to-many)
```

### Indexes Strategy
- Foreign keys: Always indexed
- Search fields: Index on `email`, `invoice_number`, `registration_number`
- Composite indexes: `(company_id, created_at)` for common queries
- Unique constraints: `email`, `registration_number`, `tin_number`

### Soft Deletes
Applied to:
- users
- companies
- invoices

NOT applied to:
- invoice_items (cascade delete)
- lhdn_credentials (hard delete)
- usage_logs (never delete)

---

## Service Layer Architecture

### LhdnService
**Purpose:** Handle all LHDN API interactions

**Methods:**
- `authenticate(Company $company): string` - Get OAuth token
- `submitInvoice(Invoice $invoice): array` - Submit to LHDN
- `getInvoiceStatus(string $submissionId): array` - Check status
- `formatInvoiceData(Invoice $invoice): array` - Format payload
- `validateCredentials(...): bool` - Test connection

**Dependencies:**
- HTTP Client (Guzzle)
- Encryption service
- Logging

### UsageTrackingService
**Purpose:** Track and enforce invoice submission limits

**Methods:**
- `getCurrentMonthUsage(Company $company): int`
- `canSubmitInvoice(Company $company): bool`
- `incrementUsage(Company $company): void`
- `getRemainingQuota(Company $company): int`
- `getUsagePercentage(Company $company): float`

**Logic:**
- Checks `usage_logs` table
- Compares against subscription plan limits
- Triggers warning notifications at 80%, 90%

### SubscriptionService
**Purpose:** Manage subscription lifecycle

**Methods:**
- `isActive(Company $company): bool`
- `isExpiring(Company $company): bool`
- `getRemainingDays(Company $company): int`
- `requestUpgrade(...): void`
- `approve(Company $company, User $approvedBy): void`
- `reject(Company $company, User $rejectedBy, string $reason): void`

---

## Queue Architecture

### Queue Configuration
```env
QUEUE_CONNECTION=redis
QUEUE_DRIVER=redis
```

### Job: SubmitInvoiceToLhdnJob
**Purpose:** Background processing of LHDN submissions

**Flow:**
1. Load invoice and company data
2. Decrypt LHDN credentials
3. Authenticate with LHDN API
4. Format invoice payload
5. POST to LHDN endpoint
6. Handle response (success/failure)
7. Update invoice status
8. Increment usage log
9. Send notification email
10. Log activity

**Configuration:**
- Queue: `lhdn-submissions`
- Retry: 3 attempts
- Backoff: Exponential (1min, 5min, 15min)
- Timeout: 60 seconds

**Monitoring:**
- Use Laravel Horizon for queue monitoring
- Dashboard at `/horizon`

---

## Security Architecture

### Encryption
- **Algorithm:** AES-256-CBC (Laravel default)
- **Encrypted Fields:**
  - `lhdn_credentials.client_id`
  - `lhdn_credentials.client_secret`

### Authorization
- **Policies:** Define for all resources
- **Gate Checks:** In Filament resources
- **Tenant Scope:** Automatic via global scope

### Rate Limiting
```php
// Per user/company
RateLimiter::for('invoice-submission', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->company_id);
});
```

### Activity Logging
- Use Spatie Laravel ActivityLog
- Log all critical actions:
  - Invoice submission
  - LHDN credential changes
  - Subscription changes
  - User actions

---

## Notification System

### Channels
- Email (primary)
- Database (in-app notifications)

### Notification Types
1. **Transactional**
   - Welcome email
   - Subscription approval
   - Invoice submission result

2. **System**
   - Usage limit warnings
   - Subscription expiring
   - LHDN credential expiry

3. **Administrative**
   - New subscription request
   - System alerts

### Implementation
```php
// Send notification
$user->notify(new InvoiceSubmittedNotification($invoice));

// Multiple channels
public function via($notifiable): array
{
    return ['mail', 'database'];
}
```

---

## Error Handling Strategy

### Levels
1. **User-Facing Errors**
   - Form validation errors
   - Business logic errors (limit exceeded)
   - Friendly error messages

2. **System Errors**
   - Log to file
   - Optional notification to admin
   - Generic message to user

3. **Critical Errors**
   - LHDN API failures
   - Database connection issues
   - Log + immediate admin notification

### Implementation
```php
try {
    $this->lhdnService->submitInvoice($invoice);
} catch (LhdnApiException $e) {
    Log::error('LHDN API Error', [
        'invoice_id' => $invoice->id,
        'error' => $e->getMessage(),
        'response' => $e->getResponse()
    ]);

    $invoice->update([
        'lhdn_status' => 'rejected',
        'lhdn_error_message' => $e->getMessage()
    ]);
}
```

---

## Caching Strategy

### What to Cache
- Subscription plans (rarely change)
- Company subscription status (TTL: 1 hour)
- LHDN OAuth tokens (TTL: until expiry)
- Usage counts (TTL: 5 minutes)

### Cache Keys Convention
```php
"company.{$companyId}.subscription.status"
"company.{$companyId}.usage.{$year}.{$month}"
"lhdn.token.{$companyId}"
```

### Cache Invalidation
- On subscription change
- On invoice submission (usage count)
- On LHDN credential update

---

## File Storage

### Directories
```
storage/
├── app/
│   ├── public/
│   │   └── payment-proofs/
│   │       └── {company_id}/
│   │           └── {filename}
│   └── invoices/
│       └── {company_id}/
│           └── {invoice_uuid}.pdf
```

### Security
- Payment proofs: Not publicly accessible
- Use signed URLs for downloads
- Validate file types on upload

---

## Deployment Considerations

### Required Services
- PHP 8.2+ with required extensions
- MySQL 8.0+
- Redis (for cache and queues)
- Supervisor (for queue workers)
- SMTP server for emails

### Queue Workers
```bash
php artisan queue:work redis --queue=lhdn-submissions --tries=3
```

### Scheduled Tasks
```php
// In app/Console/Kernel.php
$schedule->job(new SendSubscriptionExpiryReminder)->daily();
```

### Monitoring
- Laravel Horizon for queues
- Laravel Telescope (dev only)
- Custom health check endpoint

---

## Future Considerations

### Phase 2 Enhancements
- API for third-party integrations
- Webhook support
- Advanced analytics dashboard
- Multi-user per company
- Role-based permissions (Spatie)

### Scalability
- Horizontal scaling for queue workers
- Read replicas for database
- CDN for static assets
- API rate limiting per company

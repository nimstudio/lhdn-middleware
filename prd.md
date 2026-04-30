📋 Product Requirements Document
LHDN Invoice Submission Middleware SaaS
Project Overview
A Laravel-based SaaS application that allows Malaysian companies to submit invoices to LHDN (MyInvois system) through a user-friendly interface. Companies can manage invoices, track submissions, and integrate via API in future phases.

Tech Stack
Backend

Laravel 11.x
PHP 8.2+
MySQL 8.0+
Redis (Queue & Cache)

Frontend

Filament 3.x (Application Panel)
Blade + Tailwind CSS (Landing Page)
Alpine.js (Interactive elements)
Livewire 3.x

Key Packages
json{
    "laravel/framework": "^11.0",
    "filament/filament": "^3.0",
    "laravel/sanctum": "^4.0",
    "spatie/laravel-permission": "^6.0",
    "spatie/laravel-activitylog": "^4.0",
    "guzzlehttp/guzzle": "^7.0",
    "laravel/horizon": "^5.0",
    "barryvdh/laravel-dompdf": "^2.0"
}

Database Schema
sql-- Users Table (Enhanced)
users
├── id (bigint, PK)
├── name (string)
├── email (string, unique)
├── email_verified_at (timestamp, nullable)
├── password (string)
├── company_id (bigint, FK, nullable)
├── is_super_admin (boolean, default: false)
├── status (enum: active, suspended, default: active)
├── remember_token (string, nullable)
├── timestamps
└── soft_deletes

-- Companies Table (Tenant)
companies
├── id (bigint, PK)
├── uuid (string, unique) -- For public reference
├── name (string)
├── registration_number (string, unique) -- SSM number
├── tin_number (string, unique) -- Tax Identification Number
├── email (string)
├── phone (string)
├── address (text)
├── city (string)
├── state (string)
├── postcode (string)
├── country (string, default: 'Malaysia')
├── status (enum: pending, active, suspended, cancelled)
├── onboarding_completed (boolean, default: false)
├── subscription_plan_id (bigint, FK, nullable)
├── subscription_status (enum: pending, active, expired, cancelled)
├── subscription_starts_at (date, nullable)
├── subscription_ends_at (date, nullable)
├── subscription_payment_proof (string, nullable) -- File path
├── subscription_approved_by (bigint, FK, nullable)
├── subscription_approved_at (timestamp, nullable)
├── timestamps
└── soft_deletes

-- LHDN Credentials Table (Encrypted)
lhdn_credentials
├── id (bigint, PK)
├── company_id (bigint, FK, unique)
├── client_id (text) -- Encrypted
├── client_secret (text) -- Encrypted
├── mode (enum: uat, production, default: uat)
├── last_token_refresh (timestamp, nullable)
├── token_expires_at (timestamp, nullable)
├── status (enum: active, expired, invalid)
├── created_by (bigint, FK)
├── updated_by (bigint, FK, nullable)
├── timestamps

-- Subscription Plans Table
subscription_plans
├── id (bigint, PK)
├── name (string) -- Starter, Business, Enterprise
├── slug (string, unique)
├── description (text, nullable)
├── price_annually (decimal 10,2)
├── invoice_limit_monthly (integer) -- Max invoices per month
├── features (json) -- Array of features
├── is_active (boolean, default: true)
├── sort_order (integer, default: 0)
├── timestamps

-- Invoices Table
invoices
├── id (bigint, PK)
├── company_id (bigint, FK)
├── uuid (string, unique)
├── invoice_number (string)
├── invoice_date (date)
├── due_date (date, nullable)
├── customer_name (string)
├── customer_tin (string, nullable)
├── customer_registration_number (string, nullable)
├── customer_email (string, nullable)
├── customer_phone (string, nullable)
├── customer_address (text, nullable)
├── currency (string, default: 'MYR')
├── subtotal (decimal 10,2)
├── tax_amount (decimal 10,2, default: 0)
├── discount_amount (decimal 10,2, default: 0)
├── total_amount (decimal 10,2)
├── notes (text, nullable)
├── lhdn_status (enum: draft, pending, submitted, accepted, rejected)
├── lhdn_submission_id (string, nullable) -- LHDN reference
├── lhdn_submitted_at (timestamp, nullable)
├── lhdn_response (json, nullable) -- Full API response
├── lhdn_error_message (text, nullable)
├── submitted_by (bigint, FK, nullable)
├── created_by (bigint, FK)
├── timestamps
└── soft_deletes

-- Invoice Items Table
invoice_items
├── id (bigint, PK)
├── invoice_id (bigint, FK)
├── description (string)
├── quantity (decimal 10,2)
├── unit_price (decimal 10,2)
├── tax_rate (decimal 5,2, default: 0) -- Percentage
├── tax_amount (decimal 10,2, default: 0)
├── discount_rate (decimal 5,2, default: 0)
├── discount_amount (decimal 10,2, default: 0)
├── line_total (decimal 10,2)
├── sort_order (integer, default: 0)
├── timestamps

-- Usage Tracking Table
usage_logs
├── id (bigint, PK)
├── company_id (bigint, FK)
├── year (integer)
├── month (integer)
├── invoice_count (integer, default: 0)
├── last_invoice_at (timestamp, nullable)
├── timestamps
└── unique(company_id, year, month)

-- Activity Log (via spatie/activitylog)
activity_log
├── id (bigint, PK)
├── log_name (string, nullable)
├── description (string)
├── subject_type (string, nullable)
├── subject_id (bigint, nullable)
├── causer_type (string, nullable)
├── causer_id (bigint, nullable)
├── properties (json, nullable)
├── company_id (bigint, nullable) -- Added for tenant filtering
├── timestamps
└── indexes on subject, causer, company_id

-- Notifications Table (Laravel default)
notifications
├── id (uuid, PK)
├── type (string)
├── notifiable_type (string)
├── notifiable_id (bigint)
├── data (text)
├── read_at (timestamp, nullable)
├── timestamps

User Flows
1. Registration & Onboarding Flow
1. User lands on homepage
   └─> Clicks "Get Started" / "Sign Up"

2. Registration Page
   ├─> Enter: Name, Email, Password
   ├─> Validates email format
   └─> Creates user account (status: unverified)

3. Email Verification
   ├─> Send verification email
   ├─> User clicks verification link
   └─> Status: email_verified

4. First Login (After Email Verified)
   └─> Redirect to Company Onboarding Page

5. Company Information Form (MANDATORY)
   ├─> Company Name *
   ├─> Registration Number (SSM) *
   ├─> TIN Number *
   ├─> Email *
   ├─> Phone *
   ├─> Address *
   ├─> City *
   ├─> State *
   ├─> Postcode *
   └─> Submit → Creates company, links user

6. Subscription Plan Selection
   ├─> Display 3 plans with features
   ├─> User selects plan
   ├─> Upload bank transfer proof
   └─> Status: pending approval

7. Waiting for Approval Screen
   └─> "Your subscription is pending admin approval"

8. Super Admin Approves
   └─> Status changes to: active
   └─> Email notification sent to user

9. User can now access:
   ├─> Dashboard
   ├─> Invoice Management
   ├─> LHDN Credentials Setup
   └─> Profile Settings
2. LHDN Credentials Setup Flow
1. Navigate to Settings → LHDN Credentials
   
2. Form Fields:
   ├─> Client ID *
   ├─> Client Secret * (password field)
   └─> Mode * (UAT / Production)

3. Validation:
   ├─> Test connection to LHDN API
   └─> If successful: Save encrypted
       If failed: Show error message

4. Saved credentials:
   └─> Used for all invoice submissions
3. Invoice Creation & Submission Flow
1. Dashboard → Create Invoice

2. Invoice Form:
   ├─> Invoice Number (auto-generated or manual)
   ├─> Invoice Date *
   ├─> Due Date
   │
   ├─> Customer Information:
   │   ├─> Name *
   │   ├─> TIN Number
   │   ├─> Registration Number
   │   ├─> Email
   │   ├─> Phone
   │   └─> Address
   │
   ├─> Invoice Items (Repeater):
   │   ├─> Description *
   │   ├─> Quantity *
   │   ├─> Unit Price *
   │   ├─> Tax Rate (%)
   │   └─> Discount Rate (%)
   │
   ├─> Notes (optional)
   └─> Auto-calculated: Subtotal, Tax, Total

3. Save as Draft OR Submit to LHDN

4. If "Submit to LHDN":
   ├─> Check monthly usage limit
   │   └─> If exceeded: Show upgrade prompt
   │
   ├─> Validate LHDN credentials exist
   │   └─> If not: Redirect to setup
   │
   ├─> Dispatch queue job: SubmitInvoiceToLhdnJob
   │
   └─> Show success message: "Invoice queued for submission"

5. Background Job Process:
   ├─> Authenticate with LHDN API
   ├─> Format invoice data (from your existing MVP logic)
   ├─> POST to LHDN endpoint
   ├─> Handle response:
   │   ├─> Success: Update status to 'accepted'
   │   └─> Failure: Update status to 'rejected', store error
   │
   ├─> Increment usage_logs for current month
   └─> Send email notification to user

6. User views invoice:
   └─> See status badge (Submitted, Accepted, Rejected)
   └─> View LHDN response details if failed

Features Specification
A. Landing Page (Custom Blade)
Location: resources/views/landing/
Sections:

Hero Section

Headline: "Simplify Your LHDN Invoice Submissions"
Subheadline: "Automate Malaysian e-Invoice compliance with our secure middleware"
CTA: "Get Started" button → /register
Image/illustration of dashboard


Features Section

   ✓ Automated LHDN Submission
   ✓ Secure Credential Storage
   ✓ Real-time Status Tracking
   ✓ Usage Analytics
   ✓ Annual Subscription Plans

Pricing Section

3 plans displayed in cards
Features comparison
CTA: "Choose Plan" → /register


Footer

Contact info
Links: Privacy Policy, Terms of Service
Copyright



Routes:
phpRoute::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');

B. Authentication System
Using: Laravel Breeze + Custom enhancements
Pages:

/register - Registration form
/login - Login form
/forgot-password - Password reset request
/reset-password/{token} - Password reset form
/verify-email - Email verification notice
/email/verify/{id}/{hash} - Email verification handler

Email Templates:

VerifyEmailNotification - Verify email address
ResetPasswordNotification - Password reset
WelcomeNotification - After email verified

Middleware:
php'verified' => EnsureEmailIsVerified::class,
'onboarded' => EnsureCompanyOnboarded::class,
'subscribed' => EnsureActiveSubscription::class,

C. Company Onboarding (One-time Forced Flow)
Route: /onboarding/company
Middleware: auth, verified, !onboarded
Form Fields:
php[
    'company_name' => 'required|string|max:255',
    'registration_number' => 'required|string|unique:companies',
    'tin_number' => 'required|string|unique:companies',
    'email' => 'required|email',
    'phone' => 'required|string',
    'address' => 'required|string',
    'city' => 'required|string',
    'state' => 'required|string',
    'postcode' => 'required|string',
]
Process:

Create company record
Link user to company (user.company_id)
Set companies.onboarding_completed = true
Redirect to /onboarding/subscription


D. Subscription Selection & Approval
Route: /onboarding/subscription
Flow:

Display 3 subscription plans (from DB)
User selects plan → /subscription/request
Form:

   - Selected Plan (read-only)
   - Upload Payment Proof * (PDF/Image)
   - Notes (optional)

Submit → Creates subscription request

php   company.subscription_plan_id = selected_plan
   company.subscription_status = 'pending'
   company.subscription_payment_proof = uploaded_file

Notify super admin via email
Redirect to /subscription/pending - Waiting screen

Super Admin Panel (Filament):

View pending subscriptions
Actions: Approve / Reject
On Approve:

php  company.subscription_status = 'active'
  company.subscription_starts_at = now()
  company.subscription_ends_at = now()->addYear()
  company.subscription_approved_by = auth()->id()
  company.subscription_approved_at = now()

Send approval email to user
User can now access full app


E. Filament Application Panel
Panel: /app (after login + onboarding + subscription active)
Global Middleware:
phpauth, verified, onboarded, subscribed
Tenant Scoping:
All queries auto-scoped to auth()->user()->company_id
Dashboard Widgets:

Monthly Usage Widget

Invoices used this month: X / Y
Progress bar
Days remaining in subscription


Recent Invoices Widget

Last 5 invoices with status


Submission Stats Widget

Total submitted this month
Success rate
Failed submissions



Resources:
1. Invoice Resource
phpLocation: app/Filament/Resources/InvoiceResource.php

List View:
- Columns: Invoice #, Customer, Date, Amount, LHDN Status, Actions
- Filters: Status, Date Range
- Search: Invoice number, customer name
- Bulk Actions: Delete
- Custom Action: "Submit to LHDN" (for drafts)

Form:
- Section 1: Invoice Details
  - invoice_number, invoice_date, due_date
- Section 2: Customer Information
  - customer_name*, customer_tin, customer_registration_number
  - customer_email, customer_phone, customer_address
- Section 3: Invoice Items (Repeater)
  - description*, quantity*, unit_price*
  - tax_rate, discount_rate
  - line_total (calculated)
- Section 4: Summary
  - subtotal, tax_amount, discount_amount, total_amount (calculated)
  - notes (textarea)

View Page:
- Display invoice details
- Show LHDN submission history
- Action: "Submit to LHDN" button
- Action: "Download PDF" button
- Show LHDN response if submitted

Custom Actions:
- submitToLhdn()
  - Check usage limit
  - Check LHDN credentials
  - Dispatch SubmitInvoiceToLhdnJob
  - Show success notification
2. Settings Pages
Profile Settings: app/Filament/Pages/ProfileSettings.php
php- Edit user name
- Change email (with reverification)
- Change password
- Two-factor authentication (future)
Company Settings: app/Filament/Pages/CompanySettings.php
php- View/Edit company information
- Cannot change: registration_number, tin_number (immutable)
- Can edit: name, email, phone, address
LHDN Credentials: app/Filament/Pages/LhdnCredentials.php
phpForm:
- client_id (TextInput, password type)
- client_secret (TextInput, password type)
- mode (Select: UAT, Production)
- Test Connection button (AJAX)

Actions:
- Save (encrypts before saving)
- Test Connection (validates with LHDN API)

Security:
- Only company owner can edit
- Show masked values if already saved
- Activity log all changes
Subscription Info: app/Filament/Pages/SubscriptionInfo.php
phpDisplay:
- Current plan name
- Monthly invoice limit
- Invoices used this month
- Subscription period
- Renewal date
- Status badge

Actions:
- Request Upgrade (modal form)
  - Select new plan
  - Upload payment proof
  - Submit request

F. Super Admin Panel
Panel: /admin (separate Filament panel)
Access: Only users.is_super_admin = true
Resources:
1. Companies Resource
phpList:
- All companies
- Filters: Status, Subscription Status
- Actions: Edit, Suspend, Activate

Form:
- View all company details
- Edit subscription manually
- View usage statistics
- View activity log
2. Subscription Requests Resource
phpList:
- Pending subscription requests
- Columns: Company, Plan, Payment Proof, Requested Date
- Actions: Approve, Reject

View:
- Company details
- Selected plan
- Payment proof (viewable/downloadable)
- Actions: Approve / Reject with notes
3. Subscription Plans Resource
phpCRUD for plans:
- name, slug, description
- price_annually
- invoice_limit_monthly
- features (repeater)
- is_active, sort_order
4. Users Resource
phpList:
- All users across all companies
- Filters: Company, Status, Email Verified
- Actions: Edit, Suspend, Make Admin

Form:
- View user details
- View associated company
- View activity log
- Actions: Reset password, Verify email manually
5. Activity Log Resource
php- Read-only view of all system activity
- Filters: User, Company, Action Type, Date
- Search: Description, Subject

Jobs & Queues
SubmitInvoiceToLhdnJob
phpLocation: app/Jobs/SubmitInvoiceToLhdnJob.php

Process:
1. Load invoice and company
2. Get LHDN credentials (decrypt)
3. Authenticate with LHDN API (OAuth token)
4. Format invoice data (use your existing MVP logic)
5. POST to LHDN endpoint
6. Handle response:
   - Success: Update status, save response
   - Failure: Update status, save error
7. Increment usage_logs
8. Send notification email
9. Log activity

Retry: 3 times with exponential backoff
Timeout: 60 seconds
Queue: 'lhdn-submissions'

Services
LhdnService
phpLocation: app/Services/LhdnService.php

Methods:
- authenticate(Company $company): string // Returns access token
- submitInvoice(Invoice $invoice): array // Submits and returns response
- getInvoiceStatus(string $submissionId): array
- formatInvoiceData(Invoice $invoice): array // Formats to LHDN JSON structure
- validateCredentials(string $clientId, string $clientSecret, string $mode): bool

Uses:
- Your existing MVP logic for LHDN API integration
- Laravel HTTP client
- Encryption/Decryption for credentials
UsageTrackingService
phpLocation: app/Services/UsageTrackingService.php

Methods:
- getCurrentMonthUsage(Company $company): int
- canSubmitInvoice(Company $company): bool
- incrementUsage(Company $company): void
- getRemainingQuota(Company $company): int
- getUsagePercentage(Company $company): float
SubscriptionService
phpLocation: app/Services/SubscriptionService.php

Methods:
- isActive(Company $company): bool
- isExpiring(Company $company): bool // Within 30 days
- getRemainingDays(Company $company): int
- requestUpgrade(Company $company, Plan $newPlan, $paymentProof): void
- approve(Company $company, User $approvedBy): void
- reject(Company $company, User $rejectedBy, string $reason): void

Notifications
Email Notifications
1. WelcomeNotification (After email verification)
Subject: Welcome to [App Name]!
Content: 
- Thank you for signing up
- Next steps: Complete company information
- Link to login
2. CompanyOnboardedNotification (After company info submitted)
Subject: Company Information Received
Content:
- Company details confirmed
- Next step: Select subscription plan
- Link to subscription page
3. SubscriptionRequestedNotification (To Super Admin)
Subject: New Subscription Request
Content:
- Company name
- Plan selected
- Payment proof attached
- Link to admin approval page
4. SubscriptionApprovedNotification (To User)
Subject: Your Subscription is Active!
Content:
- Plan details
- Subscription period
- Monthly invoice limit
- Link to start using the app
5. InvoiceSubmittedNotification (After LHDN submission)
Subject: Invoice Submitted to LHDN
Content:
- Invoice number
- Status (Accepted / Rejected)
- LHDN reference number (if success)
- Error details (if failed)
- Link to view invoice
6. UsageLimitWarningNotification (At 80%, 90%)
Subject: Invoice Limit Warning
Content:
- Current usage: X / Y
- Percentage used
- Call to action: Upgrade plan
- Link to subscription page
7. UsageLimitReachedNotification (At 100%)
Subject: Monthly Invoice Limit Reached
Content:
- Limit reached message
- Call to action: Upgrade to continue
- Link to upgrade request form
8. SubscriptionExpiringNotification (30 days before expiry)
Subject: Subscription Expiring Soon
Content:
- Expiry date
- Renewal instructions
- Contact admin for renewal

Middleware
Custom Middleware
1. EnsureCompanyOnboarded
phpLocation: app/Http/Middleware/EnsureCompanyOnboarded.php

Logic:
if (!auth()->user()->company || !auth()->user()->company->onboarding_completed) {
    return redirect('/onboarding/company');
}
2. EnsureActiveSubscription
phpLocation: app/Http/Middleware/EnsureActiveSubscription.php

Logic:
$company = auth()->user()->company;

if (!$company->subscription_status === 'active') {
    return redirect('/subscription/pending')
        ->with('error', 'Your subscription is pending approval');
}

if ($company->subscription_ends_at < now()) {
    return redirect('/subscription/expired')
        ->with('error', 'Your subscription has expired');
}
3. CheckInvoiceLimit
phpLocation: app/Http/Middleware/CheckInvoiceLimit.php

Logic:
$company = auth()->user()->company;
$usage = app(UsageTrackingService::class)->getCurrentMonthUsage($company);
$limit = $company->plan->invoice_limit_monthly;

if ($usage >= $limit) {
    return redirect()->back()
        ->with('error', 'Monthly invoice limit reached. Please upgrade your plan.');
}
4. TenantScope
phpLocation: app/Http/Middleware/TenantScope.php

Logic:
- Add global scope to all queries
- Automatically filter by auth()->user()->company_id
- Prevent cross-tenant data access

File Structure
lhdn-saas/
├── app/
│   ├── Filament/
│   │   ├── Pages/
│   │   │   ├── ProfileSettings.php
│   │   │   ├── CompanySettings.php
│   │   │   ├── LhdnCredentials.php
│   │   │   └── SubscriptionInfo.php
│   │   ├── Resources/
│   │   │   ├── InvoiceResource.php
│   │   │   └── InvoiceResource/
│   │   │       ├── Pages/
│   │   │       │   ├── CreateInvoice.php
│   │   │       │   ├── EditInvoice.php
│   │   │       │   ├── ListInvoices.php
│   │   │       │   └── ViewInvoice.php
│   │   │       └── RelationManagers/
│   │   └── Widgets/
│   │       ├── MonthlyUsageWidget.php
│   │       ├── RecentInvoicesWidget.php
│   │       └── SubmissionStatsWidget.php
│   │
│   ├── Filament/Admin/  (Separate admin panel)
│   │   ├── Resources/
│   │   │   ├── CompanyResource.php
│   │   │   ├── SubscriptionRequestResource.php
│   │   │   ├── SubscriptionPlanResource.php
│   │   │   ├── UserResource.php
│   │   │   └── ActivityLogResource.php
│   │   └── Widgets/
│   │       ├── PendingSubscriptionsWidget.php
│   │       └── SystemStatsWidget.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LandingController.php
│   │   │   ├── OnboardingController.php
│   │   │   └── SubscriptionController.php
│   │   └── Middleware/
│   │       ├── EnsureCompanyOnboarded.php
│   │       ├── EnsureActiveSubscription.php
│   │       ├── CheckInvoiceLimit.php
│   │       └── TenantScope.php
│   │
│   ├── Jobs/
│   │   ├── SubmitInvoiceToLhdnJob.php
│   │   └── SendSubscriptionExpiryReminder.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── LhdnCredential.php
│   │   ├── SubscriptionPlan.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   └── UsageLog.php
│   │
│   ├── Notifications/
│   │   ├── WelcomeNotification.php
│   │   ├── CompanyOnboardedNotification.php
│   │   ├── SubscriptionRequestedNotification.php
│   │   ├── SubscriptionApprovedNotification.php
│   │   ├── InvoiceSubmittedNotification.php
│   │   ├── UsageLimitWarningNotification.php
│   │   ├── UsageLimitReachedNotification.php
│   │   └── SubscriptionExpiringNotification.php
│   │
│   ├── Observers/
│   │   ├── InvoiceObserver.php (Calculate totals)
│   │   └── CompanyObserver.php (Audit trail)
│   │
│   ├── Policies/
│   │   ├── InvoicePolicy.php
│   │   ├── CompanyPolicy.php
│   │   └── LhdnCredentialPolicy.php
│   │
│   └── Services/
│       ├── LhdnService.php
│       ├── UsageTrackingService.php
│       └── SubscriptionService.php
│
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_create_users_table.php
│   │   ├── 2024_01_02_create_companies_table.php
│   │   ├── 2024_01_03_create_lhdn_credentials_table.php
│   │   ├── 2024_01_04_create_subscription_plans_table.php
│   │   ├── 2024_01_05_create_invoices_table.php
│   │   ├── 2024_01_06_create_invoice_items_table.php
│   │   └── 2024_01_07_create_usage_logs_table.php
│   │
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── SubscriptionPlanSeeder.php
│   │   └── SuperAdminSeeder.php
│   │
│   └── factories/
│       ├── CompanyFactory.php
│       └── InvoiceFactory.php
│
├── resources/
│   ├── views/
│   │   ├── landing/
│   │   │   ├── index.blade.php
│   │   │   ├── pricing.blade.php
│   │   │   └── layouts/
│   │   │       └── app.blade.php
│   │   │
│   │   ├── onboarding/
│   │   │   ├── company.blade.php
│   │   │   └── subscription.blade.php
│   │   │
│   │   ├── subscription/
│   │   │   ├── pending.blade.php
│   │   │   └── expired.blade.php
│   │   │
│   │   └── emails/
│   │       └── (notification templates)
│   │
│   └── css/
│       └── app.css (Tailwind)
│
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── admin.php
│
├── tests/
│   ├── Feature/
│   │   ├── RegistrationTest.php
│   │   ├── OnboardingTest.php
│   │   ├── InvoiceSubmissionTest.php
│   │   └── UsageLimitTest.php
│   │
│   └── Unit/
│       ├── LhdnServiceTest.php
│       └── UsageTrackingServiceTest.php
│
└── config/
    └── filament.php

Seeder Data
SubscriptionPlanSeeder
phpSubscriptionPlan::create([
    'name' => 'Starter',
    'slug' => 'starter',
    'description' => 'Perfect for small businesses',
    'price_annually' => 99.00,
    'invoice_limit_monthly' => 50,
    'features' => [
        'Up to 50 invoices per month',
        'LHDN submission',
        'Email support',
        'Activity log',
    ],
    'is_active' => true,
    'sort_order' => 1,
]);

SubscriptionPlan::create([
    'name' => 'Business',
    'slug' => 'business',
    'description' => 'For growing companies',
    'price_annually' => 299.00,
    'invoice_limit_monthly' => 200,
    'features' => [
        'Up to 200 invoices per month',
        'LHDN submission',
        'Priority email support',
        'Activity log',
        'Advanced analytics',
    ],
    'is_active' => true,
    'sort_order' => 2,
]);

SubscriptionPlan::create([
    'name' => 'Enterprise',
    'slug' => 'enterprise',
    'description' => 'Unlimited invoice submission',
    'price_annually' => 999.00,
    'invoice_limit_monthly' => 999999, // Unlimited
    'features' => [
        'Unlimited invoices',
        'LHDN submission',
        '24/7 priority support',
        'Activity log',
        'Advanced analytics',
        'Dedicated account manager',
        'Custom integrations',
    ],
    'is_active' => true,
    'sort_order' => 3,
]);
SuperAdminSeeder
phpUser::create([
    'name' => 'Super Admin',
    'email' => 'admin@lhdn-saas.com',
    'email_verified_at' => now(),
    'password' => bcrypt('password'),
    'is_super_admin' => true,
    'status' => 'active',
]);

Security Implementations
1. LHDN Credentials Encryption
php// When saving
LhdnCredential::create([
    'company_id' => $company->id,
    'client_id' => encrypt($request->client_id),
    'client_secret' => encrypt($request->client_secret),
    'mode' => $request->mode,
]);

// When using
$credentials = $company->lhdnCredential;
$clientId = decrypt($credentials->client_id);
$clientSecret = decrypt($credentials->client_secret);
2. Tenant Isolation
php// Global scope in Company model
protected static function booted()
{
    static::addGlobalScope('company', function ($query) {
        if (auth()->check() && !auth()->user()->is_super_admin) {
            $query->where('company_id', auth()->user()->company_id);
        }
    });
}
3. Policy Authorization
php// InvoicePolicy
public function viewAny(User $user)
{
    return $user->company_id !== null;
}

public function create(User $user)
{
    return $user->company?->subscription_status === 'active';
}

public function update(User $user, Invoice $invoice)
{
    return $user->company_id === $invoice->company_id
        && $invoice->lhdn_status === 'draft';
}
4. Rate Limiting
php// In RouteServiceProvider or routes/web.php
Route::middleware(['throttle:invoice-submission'])->group(function () {
    Route::post('/invoices/submit', ...);
});

// In app/Providers/RouteServiceProvider.php
RateLimiter::for('invoice-submission', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->company_id);
});

Implementation Checklist
Phase 1: Foundation (Week 1)

 Laravel 11 installation
 Install Filament 3 + required packages
 Database schema creation
 Seeders (plans, super admin)
 Authentication (Breeze)
 Email verification setup

Phase 2: Landing & Onboarding (Week 1)

 Landing page design
 Registration flow
 Company onboarding form
 Subscription plan selection
 Payment proof upload
 Pending approval screen

Phase 3: Admin Panel (Week 2)

 Super admin Filament panel
 Companies resource
 Subscription requests resource
 Approve/reject functionality
 Email notifications

Phase 4: Core Application (Week 2-3)

 Main Filament panel setup
 Dashboard with widgets
 Invoice resource (CRUD)
 Invoice form with repeater items
 Auto-calculation logic
 Profile settings page
 Company settings page
 LHDN credentials page

Phase 5: LHDN Integration (Week 3)

 LhdnService implementation
 Port existing MVP logic
 SubmitInvoiceToLhdnJob
 Queue configuration
 Test connection feature
 Error handling

Phase 6: Usage Tracking (Week 4)

 UsageTrackingService
 Usage logs table
 Monthly limit checks
 CheckInvoiceLimit middleware
 Usage widgets
 Warning notifications

Phase 7: Polish & Testing (Week 4)

 All email notifications
 Activity logging
 PDF generation (if needed)
 Error pages (403, 404, 500)
 Feature tests
 UI/UX refinements
 Documentation
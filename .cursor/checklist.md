# Master Implementation Checklist

Complete checklist for entire project. Check off items as completed.

---

## 📋 Phase 1: Foundation (Week 1)

### Environment Setup
- [ ] Install Laravel 11
- [ ] Configure `.env` file
- [ ] Test database connection
- [ ] Install Redis and configure
- [ ] Setup mail configuration

### Package Installation
- [ ] Install Filament 3: `composer require filament/filament:"^3.0"`
- [ ] Install Spatie Permission: `composer require spatie/laravel-permission`
- [ ] Install Spatie ActivityLog: `composer require spatie/laravel-activitylog`
- [ ] Install Laravel Horizon: `composer require laravel/horizon`
- [ ] Install DomPDF: `composer require barryvdh/laravel-dompdf`
- [ ] Install Laravel Breeze for auth

### Filament Setup
- [ ] Run `php artisan filament:install --panels`
- [ ] Create App panel: `php artisan filament:panel app`
- [ ] Create Admin panel: `php artisan filament:panel admin`
- [ ] Configure panel settings

### Database - Migrations
- [ ] Modify users migration
- [ ] Create companies migration
- [ ] Create subscription_plans migration
- [ ] Create lhdn_credentials migration
- [ ] Create invoices migration
- [ ] Create invoice_items migration
- [ ] Create usage_logs migration
- [ ] Add activity_log company_id column
- [ ] Run all migrations

### Database - Seeders
- [ ] Create SubscriptionPlanSeeder (3 plans)
- [ ] Create SuperAdminSeeder
- [ ] Run seeders
- [ ] Verify seeded data

### Authentication
- [ ] Install Breeze
- [ ] Customize email verification
- [ ] Test registration flow
- [ ] Test email verification
- [ ] Test login/logout

---

## 📋 Phase 2: Landing & Onboarding (Week 1)

### Landing Page
- [ ] Create landing layout (Blade + Tailwind)
- [ ] Design hero section
- [ ] Create features section
- [ ] Build pricing section
- [ ] Add footer
- [ ] Create pricing page route
- [ ] Make responsive

### Controllers
- [ ] Create LandingController
- [ ] Create OnboardingController
- [ ] Create SubscriptionController

### Company Onboarding
- [ ] Create onboarding route
- [ ] Build company info form
- [ ] Add form validation
- [ ] Create Company model
- [ ] Test company creation
- [ ] Link user to company

### Subscription Selection
- [ ] Create subscription selection page
- [ ] Display available plans
- [ ] Build request form
- [ ] Add file upload (payment proof)
- [ ] Handle form submission
- [ ] Create pending screen

### Middleware
- [ ] Create EnsureCompanyOnboarded
- [ ] Create EnsureActiveSubscription
- [ ] Register middleware
- [ ] Test middleware logic

---

## 📋 Phase 3: Admin Panel (Week 2)

### Admin Panel Setup
- [ ] Configure admin panel (`/admin`)
- [ ] Restrict to super admins only
- [ ] Create admin dashboard

### Admin Resources
- [ ] Create CompanyResource
  - [ ] List view with filters
  - [ ] View page with details
  - [ ] Edit form
  - [ ] Status actions
- [ ] Create SubscriptionRequestResource
  - [ ] List pending requests
  - [ ] View request details
  - [ ] Approve action
  - [ ] Reject action
- [ ] Create SubscriptionPlanResource
  - [ ] Full CRUD
  - [ ] Features repeater
- [ ] Create UserResource
  - [ ] List all users
  - [ ] Filter by company
  - [ ] View user details
- [ ] Create ActivityLogResource
  - [ ] Read-only view
  - [ ] Filter by company/user

### Admin Actions
- [ ] Implement approve subscription
- [ ] Implement reject subscription
- [ ] Send approval notification
- [ ] Update company dates

### Admin Widgets
- [ ] PendingSubscriptionsWidget
- [ ] SystemStatsWidget

### Notifications
- [ ] SubscriptionRequestedNotification (to admin)
- [ ] SubscriptionApprovedNotification (to user)

---

## 📋 Phase 4: Core Application (Week 2-3)

### App Panel Setup
- [ ] Configure app panel (`/app`)
- [ ] Setup middleware stack
- [ ] Create dashboard layout

### Dashboard Widgets
- [ ] MonthlyUsageWidget
- [ ] RecentInvoicesWidget
- [ ] SubmissionStatsWidget

### Models
- [ ] Create Invoice model
- [ ] Create InvoiceItem model
- [ ] Add relationships
- [ ] Add global scopes (tenant)
- [ ] Create model factories

### Invoice Resource
- [ ] Create InvoiceResource
- [ ] List view
  - [ ] Table columns
  - [ ] Filters
  - [ ] Bulk actions
- [ ] Create form
  - [ ] Invoice details section
  - [ ] Customer section
  - [ ] Items repeater
  - [ ] Summary section
- [ ] Edit form
- [ ] View page
- [ ] Custom actions (Submit to LHDN)

### Settings Pages
- [ ] ProfileSettings page
- [ ] CompanySettings page
- [ ] LhdnCredentials page
  - [ ] Encrypted fields
  - [ ] Test connection button
- [ ] SubscriptionInfo page
  - [ ] Display plan details
  - [ ] Usage statistics
  - [ ] Upgrade request

### Observers
- [ ] InvoiceObserver (auto-calculate totals)
- [ ] CompanyObserver (activity log)

### Policies
- [ ] InvoicePolicy
- [ ] CompanyPolicy
- [ ] LhdnCredentialPolicy

---

## 📋 Phase 5: LHDN Integration (Week 3)

### Service Setup
- [ ] Create LhdnService
- [ ] Add authentication method
- [ ] Add submitInvoice method
- [ ] Add getInvoiceStatus method
- [ ] Add formatInvoiceData method
- [ ] Add validateCredentials method
- [ ] Port existing MVP logic

### Queue Configuration
- [ ] Configure Redis queue
- [ ] Setup queue names
- [ ] Configure Horizon
- [ ] Test queue processing

### Jobs
- [ ] Create SubmitInvoiceToLhdnJob
- [ ] Implement job logic
- [ ] Add retry mechanism
- [ ] Add timeout handling
- [ ] Test job execution

### Integration Testing
- [ ] Test LHDN authentication
- [ ] Test invoice submission (UAT)
- [ ] Test error handling
- [ ] Test retry logic
- [ ] Validate response parsing

### Error Handling
- [ ] Create custom exceptions
- [ ] Handle API errors
- [ ] Store error messages
- [ ] Log failures
- [ ] User-friendly messages

### Notifications
- [ ] InvoiceSubmittedNotification (success)
- [ ] InvoiceSubmittedNotification (failure)

---

## 📋 Phase 6: Usage Tracking (Week 4)

### Service
- [ ] Create UsageTrackingService
- [ ] Implement getCurrentMonthUsage
- [ ] Implement canSubmitInvoice
- [ ] Implement incrementUsage
- [ ] Implement getRemainingQuota
- [ ] Implement getUsagePercentage

### Usage Model
- [ ] Create UsageLog model
- [ ] Add relationships
- [ ] Create helper methods

### Middleware
- [ ] Create CheckInvoiceLimit
- [ ] Register middleware
- [ ] Apply to invoice routes
- [ ] Test limit enforcement

### Notifications
- [ ] UsageLimitWarningNotification (80%)
- [ ] UsageLimitWarningNotification (90%)
- [ ] UsageLimitReachedNotification (100%)
- [ ] SubscriptionExpiringNotification

### Scheduled Jobs
- [ ] SendSubscriptionExpiryReminder job
- [ ] Configure scheduler
- [ ] Test scheduled execution

---

## 📋 Phase 7: Polish & Testing (Week 4)

### Remaining Notifications
- [ ] WelcomeNotification
- [ ] CompanyOnboardedNotification
- [ ] Test all notification templates

### Activity Logging
- [ ] Configure Spatie ActivityLog
- [ ] Log invoice actions
- [ ] Log credential changes
- [ ] Log subscription changes
- [ ] Add company_id to logs

### UI/UX
- [ ] Improve error messages
- [ ] Add loading states
- [ ] Improve form validation feedback
- [ ] Add success messages
- [ ] Responsive design check
- [ ] Browser compatibility test

### Error Pages
- [ ] Custom 403 page
- [ ] Custom 404 page
- [ ] Custom 500 page
- [ ] Custom 419 (session expired)

### Testing
- [ ] Write RegistrationTest
- [ ] Write OnboardingTest
- [ ] Write InvoiceSubmissionTest
- [ ] Write UsageLimitTest
- [ ] Write LhdnServiceTest
- [ ] Write UsageTrackingServiceTest
- [ ] Run full test suite

### Documentation
- [ ] Update README.md
- [ ] API documentation (if applicable)
- [ ] Deployment guide
- [ ] Environment variables guide

### Performance
- [ ] Add query eager loading
- [ ] Implement caching
- [ ] Optimize database queries
- [ ] Test with large datasets

### Security Audit
- [ ] Review authorization logic
- [ ] Test tenant isolation
- [ ] Verify encryption
- [ ] Check rate limiting
- [ ] Review env variables

---

## 🚀 Deployment Preparation

- [ ] Setup production environment
- [ ] Configure queue workers
- [ ] Configure supervisor
- [ ] Setup backup strategy
- [ ] Configure monitoring
- [ ] SSL certificate
- [ ] Final security review

---

## 📊 Progress Summary

**Total Tasks:** 150+
**Completed:** 0
**In Progress:** 0
**Remaining:** 150+

---

## Notes
- Update this checklist after each session
- Mark items as complete with current date
- Add notes for complex items
- Link to commits when applicable

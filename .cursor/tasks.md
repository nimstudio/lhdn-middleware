# Task Tracker

**Priority Legend:**
- 🔴 Critical / Blocking
- 🟡 Important
- 🟢 Nice to have

---

## Current Sprint Tasks

### 🔴 CRITICAL - Must Complete First

- [ ] **Install Laravel 11**
  - Run `composer create-project laravel/laravel:^11.0 .`
  - Configure `.env` file (database, mail, queue)
  - Test basic installation

- [ ] **Install Filament 3**
  - `composer require filament/filament:"^3.0"`
  - Run `php artisan filament:install --panels`
  - Create two panels: `app` and `admin`

- [ ] **Database Setup**
  - Create all migrations (see database schema in PRD)
  - Run migrations
  - Verify all tables created correctly

---

### 🟡 IMPORTANT - Next Priority

- [ ] **Create Seeders**
  - SubscriptionPlanSeeder (3 plans: Starter, Business, Enterprise)
  - SuperAdminSeeder (admin@lhdn-saas.com)
  - Run seeders and verify

- [ ] **Authentication Setup**
  - Install Laravel Breeze
  - Customize for email verification flow
  - Add custom middleware (EnsureCompanyOnboarded, EnsureActiveSubscription)

- [ ] **Landing Page**
  - Create landing layout with Tailwind
  - Homepage with hero, features, pricing sections
  - Pricing page
  - Link to registration

---

### 🟢 UPCOMING

- [ ] Company onboarding form
- [ ] Subscription selection UI
- [ ] Admin panel resources
- [ ] Invoice management
- [ ] LHDN integration

---

## Backlog

### Models to Create
1. User (extend default)
2. Company
3. LhdnCredential
4. SubscriptionPlan
5. Invoice
6. InvoiceItem
7. UsageLog

### Services to Create
1. LhdnService
2. UsageTrackingService
3. SubscriptionService

### Jobs to Create
1. SubmitInvoiceToLhdnJob
2. SendSubscriptionExpiryReminder

### Middleware to Create
1. EnsureCompanyOnboarded
2. EnsureActiveSubscription
3. CheckInvoiceLimit
4. TenantScope

### Notifications to Create
1. WelcomeNotification
2. CompanyOnboardedNotification
3. SubscriptionRequestedNotification
4. SubscriptionApprovedNotification
5. InvoiceSubmittedNotification
6. UsageLimitWarningNotification
7. UsageLimitReachedNotification
8. SubscriptionExpiringNotification

---

## Blocked Tasks
*No blocked tasks yet*

---

## Completed Tasks
*No completed tasks yet*

---

## Notes
- Keep this file updated after each work session
- Move completed tasks to "Completed Tasks" section with date
- Add blockers immediately when discovered

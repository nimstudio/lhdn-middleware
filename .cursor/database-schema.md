# Database Schema Reference

Quick reference for database structure. See `prd.md` for full details.

---

## Core Tables

### users
```sql
id, name, email, email_verified_at, password,
company_id (FK), is_super_admin, status,
remember_token, timestamps, soft_deletes
```
**Key:** User can belong to one company

---

### companies
```sql
id, uuid, name, registration_number (unique),
tin_number (unique), email, phone,
address, city, state, postcode, country,
status, onboarding_completed,
subscription_plan_id (FK), subscription_status,
subscription_starts_at, subscription_ends_at,
subscription_payment_proof, subscription_approved_by (FK),
subscription_approved_at, timestamps, soft_deletes
```
**Key:** Root tenant entity

---

### lhdn_credentials
```sql
id, company_id (FK, unique),
client_id (encrypted), client_secret (encrypted),
mode (enum: uat, production),
last_token_refresh, token_expires_at,
status, created_by (FK), updated_by (FK),
timestamps
```
**Key:** One-to-one with company

---

### subscription_plans
```sql
id, name, slug (unique), description,
price_annually, invoice_limit_monthly,
features (json), is_active, sort_order,
timestamps
```
**Key:** Seeded with 3 plans

---

### invoices
```sql
id, company_id (FK), uuid (unique),
invoice_number, invoice_date, due_date,
customer_name, customer_tin, customer_registration_number,
customer_email, customer_phone, customer_address,
currency, subtotal, tax_amount, discount_amount, total_amount,
notes,
lhdn_status (enum), lhdn_submission_id,
lhdn_submitted_at, lhdn_response (json), lhdn_error_message,
submitted_by (FK), created_by (FK),
timestamps, soft_deletes
```
**Key:** Tenant-scoped by company_id

---

### invoice_items
```sql
id, invoice_id (FK),
description, quantity, unit_price,
tax_rate, tax_amount,
discount_rate, discount_amount,
line_total, sort_order,
timestamps
```
**Key:** Cascade delete with invoice

---

### usage_logs
```sql
id, company_id (FK),
year, month, invoice_count,
last_invoice_at,
timestamps
UNIQUE(company_id, year, month)
```
**Key:** Track monthly usage per company

---

## Supporting Tables

### activity_log (Spatie)
```sql
id, log_name, description,
subject_type, subject_id,
causer_type, causer_id,
properties (json),
company_id (custom),
timestamps
```

### notifications (Laravel)
```sql
id (uuid), type,
notifiable_type, notifiable_id,
data (text), read_at,
timestamps
```

---

## Relationships Overview

```
User
├── belongsTo Company
└── hasMany Invoice (as created_by)

Company
├── belongsTo SubscriptionPlan
├── hasOne LhdnCredential
├── hasMany Invoice
├── hasMany UsageLog
└── hasMany User

Invoice
├── belongsTo Company
├── belongsTo User (created_by, submitted_by)
└── hasMany InvoiceItem

InvoiceItem
└── belongsTo Invoice

SubscriptionPlan
└── hasMany Company

LhdnCredential
└── belongsTo Company

UsageLog
└── belongsTo Company
```

---

## Indexes to Create

**Primary Indexes:**
- All foreign keys
- `users.email` (unique)
- `companies.registration_number` (unique)
- `companies.tin_number` (unique)
- `companies.uuid` (unique)
- `invoices.uuid` (unique)
- `subscription_plans.slug` (unique)

**Composite Indexes:**
- `(company_id, created_at)` on invoices
- `(company_id, year, month)` on usage_logs (unique)
- `(company_id, lhdn_status)` on invoices

**Search Indexes:**
- `invoices.invoice_number`
- `invoices.customer_name`

---

## Enum Values

### Company Status
- `pending` - Awaiting onboarding completion
- `active` - Active subscription
- `suspended` - Temporarily suspended by admin
- `cancelled` - Subscription cancelled

### Subscription Status
- `pending` - Awaiting admin approval
- `active` - Active and valid
- `expired` - Past end date
- `cancelled` - Cancelled by user or admin

### Invoice LHDN Status
- `draft` - Not yet submitted
- `pending` - In queue for submission
- `submitted` - Sent to LHDN
- `accepted` - Accepted by LHDN
- `rejected` - Rejected by LHDN

### User Status
- `active` - Can log in
- `suspended` - Cannot log in

### LHDN Mode
- `uat` - Testing environment
- `production` - Live environment

### LHDN Credential Status
- `active` - Valid credentials
- `expired` - Token expired
- `invalid` - Failed validation

---

## Migration Order

1. `users` (modify existing)
2. `companies`
3. `subscription_plans`
4. `lhdn_credentials`
5. `invoices`
6. `invoice_items`
7. `usage_logs`
8. Add foreign keys and indexes

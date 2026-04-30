# Code Conventions & Standards

**⚠️ IMPORTANT:** This project MUST follow Laravel best practices at all times.
See [laravel-best-practices.md](laravel-best-practices.md) for comprehensive guidelines.

## Laravel Conventions

### Naming Conventions

**Models:**
- Singular, PascalCase: `Company`, `Invoice`, `SubscriptionPlan`
- Use eloquent relationships properly

**Controllers:**
- Singular + Controller suffix: `InvoiceController`, `OnboardingController`
- RESTful method names: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

**Services:**
- Descriptive name + Service suffix: `LhdnService`, `UsageTrackingService`
- Place in `app/Services/`

**Jobs:**
- Descriptive action + Job suffix: `SubmitInvoiceToLhdnJob`
- Place in `app/Jobs/`

**Middleware:**
- Descriptive purpose: `EnsureCompanyOnboarded`, `CheckInvoiceLimit`
- Place in `app/Http/Middleware/`

**Migrations:**
- Format: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`
- Use descriptive action names

---

## Filament Conventions

### Resources
```php
// Location: app/Filament/Resources/InvoiceResource.php
// Generated pages go in: app/Filament/Resources/InvoiceResource/Pages/
```

### Pages
```php
// Custom pages: app/Filament/Pages/CompanySettings.php
// Use descriptive names matching their purpose
```

### Widgets
```php
// Location: app/Filament/Widgets/MonthlyUsageWidget.php
// Suffix all widgets with "Widget"
```

---

## Database Conventions

### Table Names
- Plural, snake_case: `companies`, `invoices`, `invoice_items`

### Column Names
- snake_case: `company_id`, `created_at`, `subscription_status`

### Foreign Keys
- Format: `{model}_id` (e.g., `company_id`, `user_id`)
- Always add indexes on foreign keys
- Use cascading deletes where appropriate

### Enums
- Use native PHP enums (PHP 8.1+) or database enums
- Example statuses:
  - Company: `pending`, `active`, `suspended`, `cancelled`
  - Invoice: `draft`, `pending`, `submitted`, `accepted`, `rejected`
  - Subscription: `pending`, `active`, `expired`, `cancelled`

---

## Security Standards

### Encryption
- Always encrypt sensitive data (LHDN credentials)
```php
// Encrypting
$encrypted = encrypt($value);

// Decrypting
$decrypted = decrypt($encrypted);
```

### Multi-Tenancy
- All queries must be scoped by `company_id`
- Use global scopes in models
- Super admins bypass tenant scoping
```php
protected static function booted()
{
    static::addGlobalScope('company', function ($query) {
        if (auth()->check() && !auth()->user()->is_super_admin) {
            $query->where('company_id', auth()->user()->company_id);
        }
    });
}
```

### Authorization
- Use Laravel Policies for all resources
- Check permissions in Filament resources
```php
public static function canViewAny(): bool
{
    return auth()->user()->can('viewAny', Invoice::class);
}
```

---

## Code Quality Standards

### Comments
- Use docblocks for all public methods
- Explain complex business logic
- Keep comments up-to-date

### Type Hints
- Always use type hints for parameters and return types
```php
public function submitInvoice(Invoice $invoice): array
{
    // implementation
}
```

### Validation
- Always validate user input
- Use Form Requests for complex validation
- Place validation rules in dedicated Request classes

### Error Handling
- Use try-catch for external API calls
- Log all errors
- Return user-friendly messages
```php
try {
    $this->lhdnService->submitInvoice($invoice);
} catch (\Exception $e) {
    Log::error('LHDN submission failed', [
        'invoice_id' => $invoice->id,
        'error' => $e->getMessage()
    ]);
    throw $e;
}
```

---

## Testing Standards

### Test Structure
```
tests/
├── Feature/         # Integration tests
│   ├── RegistrationTest.php
│   ├── OnboardingTest.php
│   └── InvoiceSubmissionTest.php
└── Unit/            # Unit tests
    ├── LhdnServiceTest.php
    └── UsageTrackingServiceTest.php
```

### Naming
- Test methods: `test_descriptive_name_of_what_is_tested`
- Example: `test_user_cannot_submit_invoice_without_lhdn_credentials`

---

## Git Conventions

### Commit Messages
```
type(scope): Brief description

Optional detailed explanation
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `refactor`: Code refactoring
- `docs`: Documentation
- `test`: Tests
- `chore`: Maintenance

**Examples:**
```
feat(invoice): Add LHDN submission functionality
fix(auth): Correct email verification redirect
refactor(service): Improve LhdnService error handling
```

### Branch Naming
- Feature: `feature/invoice-submission`
- Bugfix: `fix/email-verification-bug`
- Hotfix: `hotfix/critical-security-issue`

---

## Documentation

### Inline Documentation
- Document all public methods
- Explain complex business logic
- Keep README.md updated

### API Documentation
- Document all API endpoints (Phase 2)
- Use clear examples
- Include error responses

---

## Performance

### Query Optimization
- Use eager loading to prevent N+1 queries
```php
Invoice::with('items', 'company')->get();
```

### Caching
- Cache frequently accessed data
- Use Redis for session and cache
- Clear cache appropriately

### Queue Jobs
- Use queues for long-running tasks
- Always implement job retry logic
- Monitor queue performance with Horizon

---

## Filament Specific

### Form Fields
- Use descriptive labels
- Add helpful placeholder text
- Provide validation feedback

### Tables
- Show relevant columns only
- Add filters for common searches
- Use bulk actions wisely

### Actions
- Use descriptive button labels
- Confirm destructive actions
- Provide feedback notifications

---

## Environment Variables

### Required Variables
```env
APP_NAME="LHDN Middleware"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lhdn_middleware
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
# ... mail config

# LHDN API
LHDN_UAT_BASE_URL=https://uat-api.myinvois.hasil.gov.my
LHDN_PROD_BASE_URL=https://api.myinvois.hasil.gov.my
```

---

## Notes
- Follow PSR-12 coding standards
- Use Laravel best practices
- Keep code DRY (Don't Repeat Yourself)
- Write tests for critical functionality
- Document complex business logic

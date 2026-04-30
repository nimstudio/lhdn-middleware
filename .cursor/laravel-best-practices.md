# Laravel Best Practices Guide

This document outlines Laravel best practices that MUST be followed throughout this project.

**📋 NOTE:** This project uses **Laravel Boost MCP** - see guidelines in [CLAUDE.md](../CLAUDE.md)

---

## 🚀 Laravel Boost MCP Integration

This project integrates **Laravel Boost MCP** which provides powerful tools:

### Available Tools
- **`search-docs`** - Search Laravel ecosystem documentation (Laravel, Filament, Livewire, Inertia, etc.)
  - Use BEFORE making code changes
  - Pass multiple broad queries: `['authentication', 'middleware']`
  - Auto-filters by installed package versions
- **`list-artisan-commands`** - Check available Artisan commands and parameters
- **`tinker`** - Execute PHP to debug code or query Eloquent models
- **`database-query`** - Read from database directly
- **`browser-logs`** - Read browser errors and exceptions
- **`get-absolute-url`** - Generate correct URLs for the project

### When to Use MCP Tools
✅ **Use `search-docs` when:**
- Implementing new features with Laravel/Filament
- Unsure about correct syntax/approach
- Need version-specific documentation

✅ **Use `list-artisan-commands` when:**
- Creating migrations, models, controllers
- Need to check available make commands

✅ **Use `tinker` when:**
- Debugging Eloquent queries
- Testing business logic
- Verifying data transformations

### Important Rules
- **ALWAYS** use `search-docs` before implementing new features
- **ALWAYS** pass `--no-interaction` to Artisan commands
- **ALWAYS** run `vendor/bin/pint --dirty` before finalizing code
- **NEVER** use `env()` outside config files

---

## 🎯 Core Principles

### 1. Follow Laravel Conventions
- Use Laravel's built-in features instead of reinventing the wheel
- Follow PSR-12 coding standards
- Use Laravel naming conventions consistently
- Leverage Eloquent ORM properly

### 2. Keep Controllers Thin
Controllers should only:
- Validate requests (use Form Requests)
- Call service/repository methods
- Return responses

**❌ BAD:**
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    $invoice = new Invoice();
    $invoice->company_id = auth()->user()->company_id;
    // 50 lines of business logic here...
    $invoice->save();

    // Send email
    // Update usage
    // Log activity
}
```

**✅ GOOD:**
```php
public function store(StoreInvoiceRequest $request)
{
    $invoice = $this->invoiceService->create($request->validated());

    return redirect()->route('invoices.show', $invoice)
        ->with('success', 'Invoice created successfully');
}
```

### 3. Use Service Classes for Business Logic
Move complex business logic to dedicated service classes.

**Structure:**
```
app/Services/
├── LhdnService.php
├── UsageTrackingService.php
├── SubscriptionService.php
└── InvoiceService.php
```

**Example:**
```php
namespace App\Services;

class InvoiceService
{
    public function __construct(
        private UsageTrackingService $usageTracking,
        private LhdnService $lhdn
    ) {}

    public function create(array $data): Invoice
    {
        // Business logic here
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create($data);
            $invoice->items()->createMany($data['items']);

            activity()
                ->performedOn($invoice)
                ->log('Invoice created');

            return $invoice;
        });
    }
}
```

---

## 📝 Request Validation

### Use Form Request Classes
Never validate in controllers. Always use dedicated Form Request classes.

**Create Request:**
```bash
php artisan make:request StoreInvoiceRequest
```

**Example:**
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.min' => 'Invoice must have at least one item',
            'items.*.quantity.min' => 'Quantity must be greater than zero',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => $this->user()->company_id,
            'created_by' => $this->user()->id,
        ]);
    }
}
```

---

## 🗄️ Database Best Practices

### Use Migrations Properly
```php
// Good migration structure
public function up(): void
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->uuid('uuid')->unique();
        $table->string('invoice_number', 50);
        $table->date('invoice_date');
        $table->decimal('total_amount', 10, 2);

        // Always add indexes for foreign keys and frequently queried columns
        $table->index(['company_id', 'created_at']);
        $table->index('invoice_number');

        $table->timestamps();
        $table->softDeletes();
    });
}
```

### Use Eloquent Relationships Properly
```php
class Invoice extends Model
{
    // Define inverse relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Use eager loading to prevent N+1
    protected $with = ['items']; // Only if ALWAYS needed
}
```

### Avoid N+1 Query Problems
**❌ BAD:**
```php
$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo $invoice->company->name; // N+1 query
}
```

**✅ GOOD:**
```php
$invoices = Invoice::with('company')->get();
foreach ($invoices as $invoice) {
    echo $invoice->company->name; // Single query
}
```

### Use Query Scopes
```php
class Invoice extends Model
{
    public function scopeForCompany(Builder $query, int $companyId): void
    {
        $query->where('company_id', $companyId);
    }

    public function scopeSubmitted(Builder $query): void
    {
        $query->whereIn('lhdn_status', ['submitted', 'accepted']);
    }

    public function scopeThisMonth(Builder $query): void
    {
        $query->whereBetween('invoice_date', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ]);
    }
}

// Usage
Invoice::forCompany(auth()->user()->company_id)
    ->submitted()
    ->thisMonth()
    ->get();
```

---

## 🔐 Security Best Practices

### 1. Use Policy Classes for Authorization
```bash
php artisan make:policy InvoicePolicy --model=Invoice
```

```php
class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->company_id === $invoice->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company?->subscription_status === 'active';
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->company_id === $invoice->company_id
            && $invoice->lhdn_status === 'draft';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->company_id === $invoice->company_id
            && $invoice->lhdn_status === 'draft';
    }
}
```

### 2. Always Use Mass Assignment Protection
```php
class Invoice extends Model
{
    // Prefer fillable over guarded
    protected $fillable = [
        'company_id',
        'invoice_number',
        'invoice_date',
        'customer_name',
        'total_amount',
        // ... list all fillable fields
    ];

    // OR use guarded for critical fields
    protected $guarded = [
        'id',
        'uuid',
        'lhdn_submission_id',
        'lhdn_response',
    ];
}
```

### 3. Use Encrypted Casting for Sensitive Data
```php
class LhdnCredential extends Model
{
    protected $casts = [
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
    ];
}

// Laravel automatically encrypts/decrypts
$credential->client_id = 'sensitive_value'; // Encrypted on save
$value = $credential->client_id; // Decrypted on retrieve
```

### 4. Implement Global Scopes for Multi-Tenancy
```php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && !auth()->user()->is_super_admin) {
            $builder->where('company_id', auth()->user()->company_id);
        }
    }
}

// In Model
protected static function booted(): void
{
    static::addGlobalScope(new CompanyScope);
}
```

---

## ⚡ Performance Best Practices

### 1. Use Caching Wisely
```php
// Cache expensive queries
$plans = Cache::remember('subscription_plans', 3600, function () {
    return SubscriptionPlan::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
});

// Cache tags for better invalidation
Cache::tags(['company', "company.{$companyId}"])
    ->remember("usage.{$year}.{$month}", 300, function () use ($companyId, $year, $month) {
        return UsageLog::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    });

// Invalidate when needed
Cache::tags(['company', "company.{$companyId}"])->flush();
```

### 2. Use Chunk for Large Datasets
```php
// Bad - loads all records into memory
Invoice::where('company_id', $id)->get()->each(function ($invoice) {
    // process
});

// Good - processes in chunks
Invoice::where('company_id', $id)->chunk(100, function ($invoices) {
    foreach ($invoices as $invoice) {
        // process
    }
});
```

### 3. Use Database Transactions
```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $invoice = Invoice::create($data);
    $invoice->items()->createMany($itemsData);

    UsageLog::updateOrCreate(
        ['company_id' => $companyId, 'year' => $year, 'month' => $month],
        ['invoice_count' => DB::raw('invoice_count + 1')]
    );
});
```

### 4. Use Lazy Collections for Memory Efficiency
```php
// For very large datasets
Invoice::cursor()->each(function ($invoice) {
    // processes one at a time without loading all into memory
});
```

---

## 📬 Queue Best Practices

### 1. Always Queue Long-Running Tasks
```php
// Dispatch jobs to queue
SubmitInvoiceToLhdnJob::dispatch($invoice)
    ->onQueue('lhdn-submissions');

// Chain jobs
SubmitInvoiceToLhdnJob::withChain([
    new UpdateUsageLog($invoice),
    new SendNotification($invoice),
])->dispatch($invoice);
```

### 2. Implement Proper Job Structure
```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitInvoiceToLhdnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    public $timeout = 120;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function handle(LhdnService $lhdn): void
    {
        try {
            $response = $lhdn->submitInvoice($this->invoice);

            $this->invoice->update([
                'lhdn_status' => 'accepted',
                'lhdn_submission_id' => $response['id'],
                'lhdn_response' => $response,
            ]);
        } catch (\Exception $e) {
            $this->invoice->update([
                'lhdn_status' => 'rejected',
                'lhdn_error_message' => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Handle job failure
        Log::error('Invoice submission failed', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage(),
        ]);

        // Notify user
        $this->invoice->creator->notify(
            new InvoiceSubmissionFailed($this->invoice, $exception->getMessage())
        );
    }
}
```

---

## 🎨 Code Organization

### 1. Use Action Classes for Single-Purpose Operations
```php
namespace App\Actions;

class CalculateInvoiceTotal
{
    public function execute(Invoice $invoice): float
    {
        $subtotal = $invoice->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $tax = $invoice->items->sum('tax_amount');
        $discount = $invoice->items->sum('discount_amount');

        return $subtotal + $tax - $discount;
    }
}

// Usage
$total = app(CalculateInvoiceTotal::class)->execute($invoice);
```

### 2. Use Observers for Model Events
```php
namespace App\Observers;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->uuid)) {
            $invoice->uuid = Str::uuid();
        }

        if (empty($invoice->company_id)) {
            $invoice->company_id = auth()->user()->company_id;
        }
    }

    public function created(Invoice $invoice): void
    {
        activity()
            ->performedOn($invoice)
            ->log('Invoice created');
    }

    public function updating(Invoice $invoice): void
    {
        // Recalculate totals if items changed
        if ($invoice->isDirty('items')) {
            $invoice->total_amount = app(CalculateInvoiceTotal::class)->execute($invoice);
        }
    }
}

// Register in AppServiceProvider
Invoice::observe(InvoiceObserver::class);
```

### 3. Use Traits for Reusable Logic
```php
namespace App\Traits;

trait HasCompanyScope
{
    protected static function bootHasCompanyScope(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

// Use in models
class Invoice extends Model
{
    use HasCompanyScope;
}
```

---

## 🧪 Testing Best Practices

### 1. Write Feature Tests for User Flows
```php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_invoice(): void
    {
        $user = User::factory()->withActiveSubscription()->create();

        $response = $this->actingAs($user)
            ->post(route('invoices.store'), [
                'invoice_number' => 'INV-001',
                'invoice_date' => now()->format('Y-m-d'),
                'customer_name' => 'Test Customer',
                'items' => [
                    [
                        'description' => 'Item 1',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-001',
            'company_id' => $user->company_id,
        ]);
    }

    public function test_user_cannot_exceed_monthly_limit(): void
    {
        $user = User::factory()->withActiveSubscription('starter')->create();

        // Create 50 invoices (starter limit)
        Invoice::factory()->count(50)->for($user->company)->create();

        $response = $this->actingAs($user)
            ->post(route('invoices.store'), $this->validInvoiceData());

        $response->assertForbidden();
    }
}
```

### 2. Use Database Factories
```php
namespace Database\Factories;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'uuid' => $this->faker->uuid(),
            'invoice_number' => $this->faker->unique()->numerify('INV-####'),
            'invoice_date' => $this->faker->date(),
            'customer_name' => $this->faker->company(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'lhdn_status' => 'draft',
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'lhdn_status' => 'submitted',
            'lhdn_submitted_at' => now(),
        ]);
    }
}
```

---

## 📚 Additional Best Practices

### 1. Use Enums (PHP 8.1+)
```php
namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Submission',
            self::SUBMITTED => 'Submitted to LHDN',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'warning',
            self::SUBMITTED => 'info',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
        };
    }
}

// In model
protected $casts = [
    'lhdn_status' => InvoiceStatus::class,
];
```

### 2. Use API Resources for API Responses
```php
namespace App\Http\Resources;

class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'date' => $this->invoice_date->format('Y-m-d'),
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
            ],
            'amount' => [
                'subtotal' => $this->subtotal,
                'tax' => $this->tax_amount,
                'total' => $this->total_amount,
            ],
            'status' => [
                'value' => $this->lhdn_status->value,
                'label' => $this->lhdn_status->label(),
            ],
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

### 3. Use DTOs for Complex Data Transfer
```php
namespace App\DataTransferObjects;

class InvoiceData
{
    public function __construct(
        public readonly string $invoiceNumber,
        public readonly Carbon $invoiceDate,
        public readonly string $customerName,
        public readonly array $items,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            invoiceNumber: $data['invoice_number'],
            invoiceDate: Carbon::parse($data['invoice_date']),
            customerName: $data['customer_name'],
            items: $data['items'],
            notes: $data['notes'] ?? null,
        );
    }
}
```

---

## ✅ Checklist Before Committing

- [ ] Code follows PSR-12 standards
- [ ] All methods have type hints
- [ ] Complex logic is documented with comments
- [ ] No N+1 queries (use eager loading)
- [ ] Authorization checks in place (policies)
- [ ] Validation in Form Requests
- [ ] Business logic in Services, not Controllers
- [ ] Database transactions for multi-step operations
- [ ] Long-running tasks use queues
- [ ] Tests written for critical functionality
- [ ] No sensitive data in logs
- [ ] Proper error handling and logging

---

## 📖 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices GitHub](https://github.com/alexeymezenin/laravel-best-practices)
- [Spatie Guidelines](https://spatie.be/guidelines/laravel-php)
- [Filament Documentation](https://filamentphp.com/docs)

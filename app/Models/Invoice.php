<?php

namespace App\Models;

use App\Services\NumberToWordsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'uuid',
        'document_type',
        'original_invoice_id',
        'lhdn_uuid',
        'lhdn_internal_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'currency',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'invoice_status',
        'payment_method',
        'notes',
        'lhdn_status',
        'lhdn_submission_id',
        'lhdn_submitted_at',
        'lhdn_response',
        'lhdn_error_message',
        'submitted_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'lhdn_submitted_at' => 'datetime',
            'lhdn_response' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = Str::uuid();
            }
            if (empty($invoice->company_id) && auth()->check()) {
                $invoice->company_id = auth()->user()->company_id;
            }
            if (empty($invoice->created_by) && auth()->check()) {
                $invoice->created_by = auth()->id();
            }
        });

        // Multi-tenancy global scope
        static::addGlobalScope('company', function (Builder $query) {
            if (auth()->check() && ! auth()->user()->is_super_admin) {
                $query->where('company_id', auth()->user()->company_id);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_number', 'lhdn_status', 'total_amount'])
            ->logOnlyDirty();
    }

    /**
     * Get the company that owns the invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer for the invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who submitted the invoice.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the original invoice (for credit/debit notes).
     */
    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    /**
     * Get the credit/debit notes for this invoice.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id');
    }

    /**
     * Get the invoice items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include drafts.
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('lhdn_status', 'draft');
    }

    /**
     * Scope a query to only include submitted invoices.
     */
    public function scopeSubmitted(Builder $query): void
    {
        $query->whereIn('lhdn_status', ['submitted', 'valid', 'invalid', 'cancelled']);
    }

    /**
     * Get the amount in words.
     */
    public function getAmountInWordsAttribute()
    {
        return NumberToWordsService::convert($this->total_amount);
    }

    /**
     * Get the LHDN state code for the customer state.
     */
    public function getCustomerStateLhdnCodeAttribute(): ?string
    {
        if (! $this->customer_state) {
            return null;
        }

        // Find state by name and return LHDN code
        $state = State::where('name', $this->customer_state)->first();

        return $state?->lhdn_code;
    }

    /**
     * Get the LHDN state code for the company state.
     */
    public function getCompanyStateLhdnCodeAttribute(): ?string
    {
        if (! $this->company || ! $this->company->state) {
            return null;
        }

        return $this->company->state->lhdn_code;
    }
}

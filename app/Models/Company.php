<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Company extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'registration_number',
        'tin_number',
        'tin_verified_at',
        'tin_status',
        'last_tin_check_at',
        'tin_source',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state_id',
        'postcode',
        'business_type_id',
        'country',
        'status',
        'onboarding_completed',
        'subscription_plan_id',
        'subscription_status',
        'subscription_starts_at',
        'subscription_ends_at',
        'subscription_payment_proof',
        'subscription_approved_by',
        'subscription_approved_at',
        'invoice_prefix',
        'default_tax_rates',
        'pdf_settings',
        'default_item_classification_id',
        'api_key',
        'api_key_created_at',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_completed' => 'boolean',
            'subscription_starts_at' => 'date',
            'subscription_ends_at' => 'date',
            'subscription_approved_at' => 'datetime',
            'tin_verified_at' => 'datetime',
            'last_tin_check_at' => 'datetime',
            'default_tax_rates' => 'array',
            'pdf_settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if (empty($company->uuid)) {
                $company->uuid = Str::uuid();
            }

            // Set default tax rates for new companies if not provided
            if (empty($company->default_tax_rates)) {
                // Get the LHDN tax type IDs for proper mapping
                $noTaxType = TaxType::where('code', '06')->first(); // Not Applicable
                $salesTaxType = TaxType::where('code', '01')->first(); // Sales Tax
                $serviceTaxType = TaxType::where('code', '02')->first(); // Service Tax

                // Set default item classification (Others - code 022) - MANDATORY for LHDN compliance
                $defaultClassification = ItemClassification::where('code', '022')->first();
                if (! $defaultClassification) {
                    throw new \Exception('Default item classification (022 - Others) not found. Please seed item classifications first.');
                }
                $company->default_item_classification_id = $defaultClassification->id;

                $company->default_tax_rates = [
                    [
                        'value' => 0,
                        'label' => 'No Tax',
                        'tax_type_id' => $noTaxType?->id,
                        'tax_type_code' => '06',
                    ],
                    [
                        'value' => 6,
                        'label' => 'Sales Tax (6%)',
                        'tax_type_id' => $salesTaxType?->id,
                        'tax_type_code' => '01',
                    ],
                    [
                        'value' => 8,
                        'label' => 'Service Tax (8%)',
                        'tax_type_id' => $serviceTaxType?->id,
                        'tax_type_code' => '02',
                    ],
                ];
            }
        });
    }

    /**
     * Get the state for the company.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the business type (MSIC) for the company.
     */
    public function businessType(): BelongsTo
    {
        return $this->belongsTo(Msic::class, 'business_type_id');
    }

    /**
     * Get the subscription plan for the company.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the user who approved the subscription.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subscription_approved_by');
    }

    /**
     * Get the users for the company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the LHDN credentials for the company.
     */
    public function lhdnCredential(): HasOne
    {
        return $this->hasOne(LhdnCredential::class);
    }

    /**
     * Get the customers for the company.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the invoices for the company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the usage logs for the company.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }

    /**
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active'
            && $this->subscription_ends_at?->isFuture();
    }

    /**
     * Check if subscription is expiring soon (within 30 days).
     */
    public function isSubscriptionExpiring(): bool
    {
        return $this->subscription_ends_at?->diffInDays(now()) <= 30;
    }

    /**
     * Get the default item classification for the company.
     */
    public function defaultItemClassification(): BelongsTo
    {
        return $this->belongsTo(ItemClassification::class, 'default_item_classification_id');
    }

    /**
     * Register media collections for the company.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml'])
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('pdf_logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml'])
            ->singleFile()
            ->useDisk('public');
    }

    /**
     * Register media conversions for the company.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('logo', 'pdf_logo')
            ->nonQueued();

        $this->addMediaConversion('pdf')
            ->width(300)
            ->height(300)
            ->performOnCollections('pdf_logo')
            ->nonQueued();
    }

    /**
     * Generate a new API key for the company.
     */
    public function generateApiKey(): string
    {
        $apiKey = 'lhdn_'.Str::random(32);
        $this->api_key = $apiKey;
        $this->api_key_created_at = now();
        $this->save();

        return $apiKey;
    }

    /**
     * Check if company has an API key.
     */
    public function hasApiKey(): bool
    {
        return ! empty($this->api_key);
    }

    /**
     * Regenerate the API key.
     */
    public function regenerateApiKey(): string
    {
        return $this->generateApiKey();
    }

    /**
     * Get the masked API key for display.
     */
    public function getMaskedApiKey(): string
    {
        if (! $this->hasApiKey()) {
            return '';
        }

        return substr($this->api_key, 0, 8).'...'.substr($this->api_key, -4);
    }

    /**
     * Get the full address as a single string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state?->name,
            $this->postcode,
            $this->country === 'MYS' ? 'Malaysia' : $this->country,
        ]);

        return implode(', ', $parts);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'invoice_count',
        'last_invoice_at',
    ];

    protected function casts(): array
    {
        return [
            'last_invoice_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the usage log.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Increment the invoice count for this period.
     */
    public function incrementUsage(): void
    {
        $this->increment('invoice_count');
        $this->update(['last_invoice_at' => now()]);
    }
}

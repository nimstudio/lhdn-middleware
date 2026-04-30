<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MyInvoisCredential extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'client_id',
        'client_secret',
        'is_production',
        'access_token',
        'token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_production' => 'boolean',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'client_secret',
        'access_token',
    ];

    /**
     * Get the company that owns this credential
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all invoices for this credential
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all API logs for this credential
     */
    public function apiLogs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
    }

    /**
     * Check if the access token is valid and not expired
     */
    public function hasValidToken(): bool
    {
        return $this->access_token &&
               $this->token_expires_at &&
               $this->token_expires_at->isFuture();
    }
}

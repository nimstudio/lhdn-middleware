<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LhdnCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'client_id',
        'client_secret',
        'mode',
        'access_token',
        'token_type',
        'last_token_refresh',
        'token_expires_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'last_token_refresh' => 'datetime',
            'token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the credentials.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the credentials.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the credentials.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at?->isPast() ?? true;
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

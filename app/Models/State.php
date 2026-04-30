<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'name',
        'code',
        'lhdn_code',
    ];

    /**
     * Find a state by its LHDN code.
     */
    public static function findByLhdnCode(string $lhdnCode): ?self
    {
        return static::where('lhdn_code', $lhdnCode)->first();
    }
}

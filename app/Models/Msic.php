<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Msic extends Model
{
    protected $fillable = [
        'code',
        'description',
        'category',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class, 'business_type_id');
    }
}
